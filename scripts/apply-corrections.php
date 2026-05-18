<?php
/**
 * Apply manual corrections to resources/lang/bg/ files after DeepL pass.
 *
 * Two kinds of edits:
 *  1) Exact key-path overrides — e.g. set bg/app.php:menu.tickets = 'Съпорт билети'.
 *  2) Global string substitutions — applied to every leaf string in every bg file
 *     (e.g. "Тръбопровод" → "Pipeline", "оловото" → "потенциалния клиент").
 *
 * Run:  php scripts/apply-corrections.php
 */

error_reporting(E_ALL & ~E_DEPRECATED & ~E_NOTICE);
ini_set('display_errors', '0');

$BG_DIR = __DIR__ . '/../resources/lang/bg';

/* ---------- exact key-path overrides per file ----------------------------- */
$overrides = [
    'app.php' => [
        // Stripe stays English (brand name)
        'stripe' => 'Stripe',
        // Tickets → Съпорт билети
        'menu.tickets' => 'Съпорт билети',
        // Lead translations
        'menu.leadSource'   => 'Източник на потенциални клиенти',
        'menu.leadStatus'   => 'Статус на потенциален клиент',
        'menu.leadSettings' => 'Настройки за потенциални клиенти',
        'menu.leadReport'   => 'Отчет за потенциални клиенти',
        'Changestatus'      => 'Промяна на статус',
        'leadValue'         => 'Стойност на потенциален клиент',
        'importLead'        => 'Импорт на потенциални клиенти',
        // Export — UI loanword "Експорт", not literal "Износ"
        'exportExcel'       => 'Експорт',
    ],
    'clan.php' => [
        'project.tickets' => 'Съпорт билети',
    ],
    'modules.php' => [
        'dashboard.totalTickets' => 'Съпорт билети',
        'dashboard.ticket'       => 'Съпорт билети',
        'module.tickets'         => 'Съпорт билети',
        'lead.leadDetails'       => 'Подробности за потенциалния клиент',
        'lead.leadSource'        => 'Източник на потенциален клиент',
        'lead.leadStatus'        => 'Статус на потенциален клиент',
        'lead.leadDetail'        => 'Подробности за потенциалния клиент',
        'emailNotification.lead-notification' => 'Уведомление за потенциален клиент',
        'deal.leadSetting'       => 'Настройки за потенциални клиенти',
        'deal.addPipeline'       => 'Добави нов Pipeline',
        'deal.pipeline'          => 'Pipeline',
        'customFields.export'    => 'Разреши експорт в табличен изглед',
    ],
    'messages.php' => [
        'leadStatusChangeSuccess' => 'Промяната на статуса на потенциалния клиент беше успешна',
        'noLeadSourceAdded'       => 'Няма добавен източник на потенциални клиенти.',
        'noLeadAgentAdded'        => 'Няма добавени отговорници за потенциални клиенти.',
    ],
];

/* ---------- global string substitutions ---------------------------------- */
// Applied to every string value across every file (after the per-key overrides).
$globalReplacements = [
    // Pipeline → English (keep brand-style consistency)
    'Тръбопроводът'   => 'Pipeline-ът',
    'тръбопроводът'   => 'pipeline-ът',
    'Тръбопровода'    => 'Pipeline-а',
    'тръбопровода'    => 'pipeline-а',
    'Тръбопровод'     => 'Pipeline',
    'тръбопровод'     => 'pipeline',

    // Lead → Потенциален клиент (multi-step, longest-first so prefixes don't
    // partially match)
    'на оловото'              => 'на потенциалния клиент',
    'оловосъдържащи вещества' => 'отговорници за потенциални клиенти',
    'оловото'                 => 'потенциалния клиент',
    'на олово'                => 'на потенциални клиенти',
    'олово'                   => 'потенциален клиент',

    // Export — use the UI loanword "Експорт" instead of literal "Износ"
    'Износ' => 'Експорт',
    'износ' => 'експорт',
];

/* ---------- output formatter (same as translator script) ----------------- */
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

function setNested(array &$arr, array $path, $value): bool
{
    $ref = &$arr;
    $last = array_pop($path);
    foreach ($path as $key) {
        if (!isset($ref[$key]) || !is_array($ref[$key])) return false;
        $ref = &$ref[$key];
    }
    if (!array_key_exists($last, $ref)) return false;
    $ref[$last] = $value;
    return true;
}

function walkAndReplace(array &$arr, array $replacements, int &$changed): void
{
    foreach ($arr as $k => &$v) {
        if (is_array($v)) {
            walkAndReplace($v, $replacements, $changed);
        } elseif (is_string($v)) {
            $new = strtr($v, $replacements);
            if ($new !== $v) {
                $v = $new;
                $changed++;
            }
        }
    }
}

/* ---------- main --------------------------------------------------------- */

$totalOverridden = 0;
$totalGlobalReplaced = 0;

foreach (glob($BG_DIR . '/*.php') as $file) {
    $bn = basename($file);
    $arr = require $file;
    if (!is_array($arr)) continue;

    // 1. Per-key overrides
    $overrideCount = 0;
    if (isset($overrides[$bn])) {
        foreach ($overrides[$bn] as $keyPath => $newValue) {
            $path = explode('.', $keyPath);
            if (setNested($arr, $path, $newValue)) {
                $overrideCount++;
            } else {
                fwrite(STDERR, "  WARN: $bn key not found: $keyPath\n");
            }
        }
    }

    // 2. Global string substitutions
    $globalCount = 0;
    walkAndReplace($arr, $globalReplacements, $globalCount);

    if ($overrideCount + $globalCount > 0) {
        file_put_contents($file, "<?php\n\nreturn " . formatPhpArray($arr) . ";\n");
        printf("  %-25s overrides=%d  global=%d\n", $bn, $overrideCount, $globalCount);
        $totalOverridden += $overrideCount;
        $totalGlobalReplaced += $globalCount;
    } else {
        printf("  %-25s (no changes)\n", $bn);
    }
}

echo "\nTOTAL: $totalOverridden overrides, $totalGlobalReplaced global replacements\n";
