<?php declare(strict_types=1);

namespace Circli\Testing;

final class HttpStatusClassifier implements Classifier
{
	private $expectException = false;
	private $expectedStatuseCode = 0;

	public function classify($requestData): void
	{
		if (is_string($requestData)) {
			$rs = preg_match('/(\d{3})\_/', ltrim($requestData, '/'), $m);
			if ($rs) {
				$this->expectException = $m[1] >= 400;
				$this->expectedStatuseCode = (int)$m[1];
			}
		}
	}

	public function expectException(): bool
	{
		return $this->expectException;
	}

	public function expectedStatusCode(): int
	{
		return $this->expectedStatuseCode;
	}
}
