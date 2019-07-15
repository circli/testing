<?php declare(strict_types=1);

namespace Circli\Testing;

use Circli\Testing\Exception\FileNotFound;

final class DefaultLoader implements Loader
{
	/** @var string */
	private $basePath;

	public function __construct(string $basePath)
	{
		$this->basePath = $basePath;
	}

	public function loadJson(string $file): array
	{
		$path = $this->basePath . '/' . $file;
		if (file_exists($path)) {
			return json_decode(file_get_contents($path), true, 512, JSON_THROW_ON_ERROR);
		}
		throw new FileNotFound($path);
	}

	public function loadRequest(string $file): array
	{
		$info = $this->loadJson($file);
		$payload = $info['input'] ?? [];
		$attributes = $info['attributes'] ?? [];
		if (isset($info['input']) && is_string($info['input'])) {
			$payload = $this->loadJson($info['input']);
		}
		if (isset($info['attributes']) && is_string($info['attributes'])) {
			$attributes = $this->loadJson($info['attributes']);
		}

		return [$payload, $attributes];
	}

	/**
	 * @param string $path
	 * @return string[]
	 */
	public function getFiles(string $path): array
	{
		$baseLength = \strlen($this->basePath) + 1;
		$resourcePath = $this->basePath . "/$path/*";
		return array_filter(
			array_map(
				static function ($r) use ($baseLength) {
					return ltrim(substr($r, $baseLength), '/');
				},
				glob($resourcePath)
			),
			static function ($file) {
				return strpos($file, '_m_') !== 3;
			}
		);
	}
}
