<?php
/**
 * Bulk-translate resources/lang/eng/*.php → resources/lang/bg/*.php
 *
 * Uses batched Google Translate (free Stichoza endpoint). Groups up to ~3500
 * characters per request, separated by "\n#### N ####\n" markers that Google
 * Translate reliably preserves. Placeholders (:foo, {n}, [n,*], <tag>) are
 * masked to opaque tokens before translation and restored after, so they
 * never get mangled.
 *
 * Idempotent: bg/<file>.php already exists → skipped.
 * Fault-tolerant: any batch that fails or returns the wrong number of parts
 * falls back to per-string translation for that batch; permanent failures
 * keep the English original so the app still renders.
 *
 * Run:  php scripts/translate-lang-files.php
 */

require __DIR__ . '/../vendor/autoload.php';

use Stichoza\GoogleTranslate\GoogleTranslate;

error_reporting(E_ALL & ~E_DEPRECATED & ~E_NOTICE);
ini_set('display_errors', '0');

$SOURCE_DIR = __DIR__ . '/../resources/lang/eng';
$TARGET_DIR = __DIR__ . '/../resources/lang/bg';
$BATCH_MAX_CHARS = 3500;
$BATCH_DELAY_USEC = 300000; // 0.3s between batches
$MAX_RETRIES = 4;

if (!is_dir($TARGET_DIR)) {
    mkdir($TARGET_DIR, 0755, true);
}

$tr = new GoogleTranslate('bg', 'en');

/* ------------ placeholder masking ------------- */

function maskPlaceholders(string $str, array &$tokens): string
{
    $tokens = [];
    $counter = 0;
    $patterns = [
        '/:[a-zA-Z_][a-zA-Z0-9_]*/',
        '/\{[0-9*,\s]+\}/',
        '/\[[0-9*,\s]+\]/',
        '/<\/?[a-zA-Z][^>]*>/',
    ];
    foreach ($patterns as $pattern) {
        $str = preg_replace_callback($pattern, function ($m) use (&$tokens, &$counter) {
            $token = "@@PH{$counter}@@";
            $counter++;
            $tokens[$token] = $m[0];
            return $token;
        }, $str);
    }
    return $str;
}

function restorePlaceholders(string $str, array $tokens): string
{
    $str = strtr($str, $tokens);
    foreach ($tokens as $token => $original) {
        $pat = '/' . preg_quote($token, '/') . '/i';
        $str = preg_replace($pat, $original, $str);
    }
    return $str;
}

/* ------------ batching ------------- */

function buildBatch(array $strings, int &$start, int $maxChars): array
{
    $batch = [];
    $masked = [];
    $tokensList = [];
    $charCount = 0;
    $n = count($strings);
    while ($start < $n) {
        $orig = $strings[$start];
        $tokens = [];
        $m = maskPlaceholders($orig, $tokens);
        $entrySize = strlen($m) + 30;
        if ($batch && ($charCount + $entrySize) > $maxChars) break;
        $batch[] = $orig;
        $masked[] = $m;
        $tokensList[] = $tokens;
        $charCount += $entrySize;
        $start++;
    }
    return [$batch, $masked, $tokensList];
}

function joinBatch(array $masked): string
{
    $parts = [];
    foreach ($masked as $i => $s) {
        if ($i > 0) $parts[] = "\n#### " . $i . " ####\n";
        $parts[] = $s;
    }
    return implode("", $parts);
}

function splitBatch(string $translated, int $expectedCount): ?array
{
    if ($expectedCount === 1) return [$translated];
    $parts = preg_split('/\n+\s*####\s*\d+\s*####\s*\n+/u', $translated);
    if (!is_array($parts) || count($parts) !== $expectedCount) {
        return null;
    }
    return array_map('trim', $parts);
}

function translateBatchWithRetry(GoogleTranslate $tr, array $masked, int $maxRetries): ?array
{
    $expected = count($masked);
    if ($expected === 0) return [];
    $joined = joinBatch($masked);
    for ($attempt = 0; $attempt < $maxRetries; $attempt++) {
        try {
            $out = $tr->translate($joined);
            if ($out === null || $out === '') {
                throw new RuntimeException('empty response');
            }
            $parts = splitBatch($out, $expected);
            if ($parts !== null) return $parts;
            fwrite(STDERR, "    batch split mismatch (expected {$expected}); retrying\n");
        } catch (\Throwable $e) {
            fwrite(STDERR, "    batch error attempt " . ($attempt + 1) . ": " . substr($e->getMessage(), 0, 80) . "\n");
        }
        usleep(500000 * (int)pow(2, $attempt));
    }
    return null;
}

function translateOne(GoogleTranslate $tr, string $masked, int $maxRetries): ?string
{
    for ($attempt = 0; $attempt < $maxRetries; $attempt++) {
        try {
            $out = $tr->translate($masked);
            if ($out !== null && $out !== '') return $out;
        } catch (\Throwable $e) {
            // ignore, retry
        }
        usleep(400000 * (int)pow(2, $attempt));
    }
    return null;
}

/**
 * Translate a flat list of strings, return translated list (same indices).
 * Originals are returned for any string that ultimately fails.
 */
function translateStrings(GoogleTranslate $tr, array $strings, int $maxChars, int $delayUsec, int $maxRetries, string $fileLabel): array
{
    $result = [];
    $total = count($strings);
    $i = 0;
    $done = 0;
    while ($i < $total) {
        [$batch, $masked, $tokensList] = buildBatch($strings, $i, $maxChars);
        $parts = translateBatchWithRetry($tr, $masked, $maxRetries);
        if ($parts === null) {
            // Fallback: per-string for this batch
            fwrite(STDERR, "    fallback to per-string for batch of " . count($batch) . "\n");
            foreach ($masked as $idx => $m) {
                $single = translateOne($tr, $m, $maxRetries);
                if ($single !== null) {
                    $result[] = restorePlaceholders($single, $tokensList[$idx]);
                } else {
                    $result[] = $batch[$idx]; // keep English
                }
                usleep($delayUsec);
            }
        } else {
            foreach ($parts as $idx => $p) {
                $result[] = restorePlaceholders($p, $tokensList[$idx]);
            }
        }
        $done += count($batch);
        $pct = (int)round($done * 100 / max($total, 1));
        echo "  [$fileLabel] {$done}/{$total} ({$pct}%)\n";
        usleep($delayUsec);
    }
    return $result;
}

/* ------------ array walking ------------- */

function collectStrings(array $arr, array $path = []): array
{
    // returns list of [path, value] for every string leaf
    $out = [];
    foreach ($arr as $key => $value) {
        if (is_array($value)) {
            foreach (collectStrings($value, array_merge($path, [$key])) as $entry) {
                $out[] = $entry;
            }
        } elseif (is_string($value)) {
            $out[] = [array_merge($path, [$key]), $value];
        }
    }
    return $out;
}

function setNested(array &$arr, array $path, $value): void
{
    $ref = &$arr;
    foreach ($path as $key) {
        if (!is_array($ref)) {
            $ref = [];
        }
        if (!array_key_exists($key, $ref)) {
            $ref[$key] = [];
        }
        $ref = &$ref[$key];
    }
    $ref = $value;
}

/* ------------ output formatter ------------- */

function formatPhpArray(array $arr, int $indent = 0): string
{
    $pad = str_repeat('    ', $indent);
    $padInner = str_repeat('    ', $indent + 1);
    $lines = [];
    foreach ($arr as $key => $value) {
        $keyStr = is_int($key) ? $key : "'" . addcslashes((string)$key, "'\\") . "'";
        if (is_array($value)) {
            $lines[] = "{$padInner}{$keyStr} => " . formatPhpArray($value, $indent + 1) . ",";
        } elseif (is_string($value)) {
            $lines[] = "{$padInner}{$keyStr} => '" . addcslashes($value, "'\\") . "',";
        } elseif (is_bool($value)) {
            $lines[] = "{$padInner}{$keyStr} => " . ($value ? 'true' : 'false') . ",";
        } elseif (is_null($value)) {
            $lines[] = "{$padInner}{$keyStr} => null,";
        } else {
            $lines[] = "{$padInner}{$keyStr} => " . var_export($value, true) . ",";
        }
    }
    return "[\n" . implode("\n", $lines) . "\n{$pad}]";
}

/* ------------ main ------------- */

$files = glob($SOURCE_DIR . '/*.php');
usort($files, fn($a, $b) => filesize($a) <=> filesize($b));

echo "Source: " . realpath($SOURCE_DIR) . "\n";
echo "Target: " . realpath($TARGET_DIR) . "\n";
echo "Files:  " . count($files) . "\n\n";

$totalStart = time();
foreach ($files as $file) {
    $basename = basename($file);
    $targetFile = $TARGET_DIR . '/' . $basename;
    if (file_exists($targetFile) && filesize($targetFile) > 0) {
        echo "SKIP $basename (already translated; delete bg/$basename to redo)\n";
        continue;
    }
    $arr = require $file;
    if (!is_array($arr)) {
        echo "SKIP $basename (not array)\n";
        continue;
    }

    $entries = collectStrings($arr);
    $strings = array_map(fn($e) => $e[1], $entries);
    $paths = array_map(fn($e) => $e[0], $entries);

    echo "→ $basename (" . count($strings) . " strings)\n";
    $start = time();
    $translated = translateStrings($tr, $strings, $BATCH_MAX_CHARS, $BATCH_DELAY_USEC, $MAX_RETRIES, $basename);

    foreach ($paths as $i => $path) {
        setNested($arr, $path, $translated[$i]);
    }
    file_put_contents($targetFile, "<?php\n\nreturn " . formatPhpArray($arr) . ";\n");
    echo "✓ wrote bg/$basename in " . (time() - $start) . "s\n\n";
}
echo "=== ALL DONE in " . (time() - $totalStart) . "s ===\n";
