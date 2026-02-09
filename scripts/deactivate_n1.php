<?php
// scripts/deactivate_n1.php
// Bootstraps the Laravel app and runs a safe UPDATE to deactivate JLPT N1 kanji.

require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';

// Bootstrap the application
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

try {
    // jlpt_level is numeric in this schema; use numeric comparison to avoid type errors
    $sql = "UPDATE `kanji` SET `is_active` = 0 WHERE jlpt_level = 1 AND is_active = 1";
    $affected = DB::affectingStatement($sql);
    echo "OK: Updated {$affected} rows.\n";
} catch (Throwable $e) {
    echo "ERROR: ", $e->getMessage(), "\n";
    exit(1);
}
