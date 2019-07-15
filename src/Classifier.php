<?php declare(strict_types=1);

namespace Circli\Testing;

interface Classifier
{
	public function classify($requestData): void;

	public function expectException(): bool;

	public function expectedStatusCode(): int;
}
