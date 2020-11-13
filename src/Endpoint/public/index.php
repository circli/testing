<?php declare(strict_types=1);
error_reporting(E_ALL);

$maxDepth = 6;

$autoloader = null;
$extraRoot = getenv('TESTING_SERVER_ROOT') ?: '';
for ($i = 0; $i <= $maxDepth; $i++) {
	$base = $i ? dirname(__DIR__, $i) : __DIR__;
	$autoloader = $base . '/' . trim($extraRoot, '/') . '/vendor/autoload.php';
	if (file_exists($autoloader)) {
		break;
	}
}

if (!file_exists($autoloader)) {
	echo "Composer autoloader not found: $autoloader" . PHP_EOL;
	echo "Please issue 'composer install' and try again." . PHP_EOL;
	exit(1);
}
require $autoloader;

use App\App;
use Circli\Core\Environment;

$x = static function () {
	$initFile = getenv('TESTING_INIT_FILE');
	if ($initFile) {
		require $initFile;
	}
};
$x();

$app = new App(Environment::TESTING());
$app->run();
