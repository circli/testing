<?php declare(strict_types=1);

namespace Circli\Testing\Traits;

use Circli\Testing\Exception\FileNotFound;
use Circli\Testing\Loader;

trait LoadResourceTrait
{
	abstract protected function getLoader(): Loader;

	protected function loadResource(string $file): array
	{
		try {
			return $this->getLoader()->loadJson($file);
		}
		catch (\JsonException $e) {
			try {
				return [$this->getLoader()->loadFile($file)];
			}
			catch (FileNotFound $e) {
				$this->addWarning('Resource file not found: ' . $file);
				return [];
			}
		}
		catch (FileNotFound $e) {
			$this->addWarning('Resource file not found: ' . $file);
			return [];
		}
	}
}
