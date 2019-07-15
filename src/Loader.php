<?php declare(strict_types=1);

namespace Circli\Testing;

interface Loader
{
	public function loadJson(string $file): array;

	public function loadRequest(string $file): array;

	/**
	 * @param string $data
	 * @return string[]
	 */
	public function getFiles(string $data): array;
}
