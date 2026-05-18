<?php
/**
 * Bulk-translate resources/lang/eng/*.php → resources/lang/bg/*.php via DeepL.
 *
 * DeepL is significantly higher quality than free Google Translate for Bulgarian,
 * especially for business / formal language. Uses tag_handling=xml + ignore_tags=x
 * so :foo / {n} / [n,*] placeholders get wrapped in <x>...</x> markers that DeepL
 * preserves verbatim, then we strip the marker tags from the result.
 *
 * Batches up to 50 texts per request (DeepL hard limit). 6367 strings = ~128
 * requests, runs in ~1-2 minutes total.
 *
 * Idempotent: if bg/<file>.php exists and is non-empty, the file is skipped.
 * Delete bg/<file>.php to redo it.
 *
 * Run:  DEEPL_API_KEY=xxxxx php scripts/translate-lang-files-deepl.php
 */

require __DIR__ . '/../vendor/autoload.php';

error_reporting(E_ALL & ~E_DEPRECATED & ~E_NOTICE);
ini_set('display_errors', '0');

$DEEPL_KEY = getenv('DEEPL_API_KEY') ?: '';
if ($DEEPL_KEY === '') {
    fwrite(STDERR, "ERROR: set DEEPL_API_KEY env var\n");
    exit(1);
}

$SOURCE_DIR = __DIR__ . '/../resources/lang/eng';
$TARGET_DIR = __DIR__ . '/../resources/lang/bg';
$BATCH_SIZE = 50;          // DeepL hard limit per request
$API_BASE   = str_ends_with($DEEPL_KEY, ':fx')
              ? 'https://api-free.deepl.com/v2'
              : 'https://api.deepl.com/v2';
$MAX_RETRIES = 4;

if (!is_dir($TARGET_DIR)) mkdir($TARGET_DIR, 0755, true);

/* ----- placeholder handling ---------------------------------------------- */

function maskPlaceholders(string $s): string
{
    // DeepL HTML mode handles real HTML (incl. unclosed <br>, &laquo;) natively.
    // Wrap placeholders in <span translate="no">...</span> so DeepL leaves them
    // alone. Strip the wrapper from the result afterwards.
    $patterns = [
        '/:[a-zA-Z_][a-zA-Z0-9_]*/',
        '/\{[0-9*,\s]+\}/',
        '/\[[0-9*,\s]+\]/',
    ];
    foreach ($patterns as $p) {
        $s = preg_replace_callback($p, fn($m) => '<span translate="no">' . $m[0] . '</span>', $s);
    }
    return $s;
}

function unmaskPlaceholders(string $s): string
{
    // Strip the translate="no" wrappers we added to protect placeholders. Tag
    // attribute order/quoting may vary in DeepL output, so be permissive.
    return preg_replace('#<span\s+translate=["\']?no["\']?[^>]*>([^<]*)</span>#i', '$1', $s);
}

/* ----- DeepL API --------------------------------------------------------- */

function deeplBatch(array $texts, string $key, string $apiBase, int $maxRetries): array
{
    $params = [];
    foreach ($texts as $t) $params[] = 'text=' . urlencode($t);
    $params[] = 'source_lang=EN';
    $params[] = 'target_lang=BG';
    $params[] = 'tag_handling=html';
    $params[] = 'formality=prefer_more';  // business app — prefer formal "Вие"
    $params[] = 'preserve_formatting=1';
    $body = implode('&', $params);

    for ($attempt = 0; $attempt < $maxRetries; $attempt++) {
        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL => $apiBase . '/translate',
            CURLOPT_POST => true,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POSTFIELDS => $body,
            CURLOPT_HTTPHEADER => [
                'Authorization: DeepL-Auth-Key ' . $key,
                'Content-Type: application/x-www-form-urlencoded',
            ],
            CURLOPT_TIMEOUT => 60,
        ]);
        $resp = curl_exec($ch);
        $code = (int)curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $err = curl_error($ch);
        curl_close($ch);

        if ($code === 200 && $resp !== false) {
            $data = json_decode($resp, true);
            if (isset($data['translations']) && is_array($data['translations'])) {
                $out = array_column($data['translations'], 'text');
                if (count($out) === count($texts)) return $out;
                fwrite(STDERR, "    DeepL returned " . count($out) . " for " . count($texts) . " texts; retrying\n");
            } else {
                fwrite(STDERR, "    DeepL malformed response; retrying\n");
            }
        } elseif ($code === 429 || $code === 503) {
            fwrite(STDERR, "    DeepL rate-limited ($code); backing off\n");
        } else {
            fwrite(STDERR, "    DeepL HTTP $code attempt " . ($attempt + 1) . ": " . substr($resp ?: $err, 0, 200) . "\n");
        }
        sleep(2 << $attempt); // 2, 4, 8, 16
    }
    throw new RuntimeException("DeepL translation batch failed after $maxRetries attempts");
}

/* ----- array traversal --------------------------------------------------- */

function collectStrings(array $arr, array $path = []): array
{
    $out = [];
    foreach ($arr as $key => $value) {
        $newPath = array_merge($path, [$key]);
        if (is_array($value)) {
            foreach (collectStrings($value, $newPath) as $e) $out[] = $e;
        } elseif (is_string($value) && trim($value) !== '') {
            $out[] = [$newPath, $value];
        }
    }
    return $out;
}

function setNested(array &$arr, array $path, $value): void
{
    $ref = &$arr;
    foreach ($path as $key) {
        if (!is_array($ref)) $ref = [];
        if (!array_key_exists($key, $ref)) $ref[$key] = [];
        $ref = &$ref[$key];
    }
    $ref = $value;
}

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

/* ----- main -------------------------------------------------------------- */

$files = glob($SOURCE_DIR . '/*.php');
usort($files, fn($a, $b) => filesize($a) <=> filesize($b));

echo "Source:    " . realpath($SOURCE_DIR) . "\n";
echo "Target:    " . realpath($TARGET_DIR) . "\n";
echo "Files:     " . count($files) . "\n";
echo "API base:  $API_BASE\n";
echo "Batch:     $BATCH_SIZE strings/request, formality=prefer_more\n\n";

$globalStart = time();
foreach ($files as $file) {
    $bn = basename($file);
    $targetFile = $TARGET_DIR . '/' . $bn;
    if (file_exists($targetFile) && filesize($targetFile) > 50) {
        echo "SKIP $bn (already translated; delete bg/$bn to redo)\n";
        continue;
    }

    $arr = require $file;
    if (!is_array($arr)) {
        echo "SKIP $bn (not array)\n";
        continue;
    }

    $entries = collectStrings($arr);
    $total = count($entries);
    echo "→ $bn ($total strings)\n";

    $start = time();
    $done = 0;
    for ($i = 0; $i < $total; $i += $BATCH_SIZE) {
        $slice = array_slice($entries, $i, $BATCH_SIZE);
        $masked = array_map(fn($e) => maskPlaceholders($e[1]), $slice);
        $translated = deeplBatch($masked, $DEEPL_KEY, $API_BASE, $MAX_RETRIES);
        foreach ($slice as $idx => $entry) {
            $clean = unmaskPlaceholders($translated[$idx]);
            setNested($arr, $entry[0], $clean);
        }
        $done += count($slice);
        $pct = (int)round($done * 100 / max($total, 1));
        echo "  [$bn] $done/$total ({$pct}%)\n";
    }

    file_put_contents($targetFile, "<?php\n\nreturn " . formatPhpArray($arr) . ";\n");
    echo "✓ wrote bg/$bn in " . (time() - $start) . "s\n\n";
}

echo "=== ALL DONE in " . (time() - $globalStart) . "s ===\n";

// Print usage so the user knows how much quota was consumed.
$ch = curl_init();
curl_setopt_array($ch, [
    CURLOPT_URL => $API_BASE . '/usage',
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_HTTPHEADER => ['Authorization: DeepL-Auth-Key ' . $DEEPL_KEY],
]);
$usage = curl_exec($ch);
curl_close($ch);
echo "\nDeepL usage: $usage\n";
