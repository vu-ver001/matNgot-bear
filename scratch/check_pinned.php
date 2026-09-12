<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

foreach (App\Models\Category::all() as $c) {
    echo "ID: {$c->id} | Name: {$c->name} | is_active: " . ($c->is_active ? 1 : 0) . " | is_pinned: " . ($c->is_pinned ? 1 : 0) . "\n";
}
