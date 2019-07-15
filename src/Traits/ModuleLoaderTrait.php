<?php declare(strict_types=1);

namespace Circli\Testing\Traits;

use Circli\Testing\DefaultLoader;
use Circli\Testing\Loader;

trait ModuleLoaderTrait
{
	protected $module;

	protected static $pathCache = [];
	protected function getModuleBasePath()
	{
		if (!isset(self::$pathCache[$this->module])) {
			$reflection = new \ReflectionClass($this->module);
			self::$pathCache[$this->module] = dirname($reflection->getFileName(), 2);
		}

		return self::$pathCache[$this->module];
	}

	protected function getLoader(): Loader
	{
		if (!$this->module) {
			throw new \RuntimeException('Module variable must be specified to auto resolve path');
		}

		return new DefaultLoader($this->getModuleBasePath() . '/resources/test-data');
	}
}
