<?php

require_once 'vendor/autoload.php';

// Bootstrap Laravel
$app = require_once 'bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

// Test logging
use Illuminate\Support\Facades\Log;

echo "Testing logging...\n";

Log::info('Test log entry from script', ['test' => true, 'timestamp' => now()]);
Log::warning('Test warning from script', ['test' => true]);
Log::error('Test error from script', ['test' => true]);

echo "Log entries written. Check storage/logs/laravel.log\n";
