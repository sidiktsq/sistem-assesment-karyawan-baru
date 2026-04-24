<?php
require 'vendor/autoload.php';

use Filament\Schemas\Schema;

$reflection = new ReflectionClass(Schema::class);
$methods = array_map(fn($m) => $m->name, $reflection->getMethods());

echo implode("\n", $methods);
