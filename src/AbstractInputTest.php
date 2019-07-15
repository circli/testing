<?php declare(strict_types=1);

namespace Circli\Testing;

use PHPUnit\Framework\TestCase;
use Circli\Testing\Traits\CreateRequestTrait;

abstract class AbstractInputTest extends TestCase
{
	protected $inputs;

	use CreateRequestTrait;

	public function getClassifier(): Classifier
	{
		return new HttpStatusClassifier();
	}

	protected function createInput(string $inputCls)
	{
		return new $inputCls();
	}

	/**
	 * @dataProvider inputs
	 */
	public function testAll(string $inputCls, $requestData, array $attributes): void
	{
		$input = $this->createInput($inputCls);
		$request = $this->getMockRequest($attributes, $requestData);
		$classifier = $this->getClassifier();
		$classifier->classify($requestData);

		if ($classifier->expectException()) {
			$this->expectException(\DomainException::class);
		}

		$result = $input($request);
		$this->assertNotNull($result);
	}

	public function inputs(): array
	{
		if (!$this->inputs) {
			$this->addWarning('No inputs specified');
			return [];
		}

		$inputs = [];
		foreach ($this->inputs as $cls => $data) {
			if (is_string($data)) {
				$files = $this->getLoader()->getFiles($data);
				foreach ($files as $file) {
					$inputs[] = [
						$cls,
						$file,
						[],
					];
				}
			}
			else {
				$inputs[] = [
					$cls,
					$data[0],
					$data[1] ?? [],
				];
			}
		}

		return $inputs;
	}
}
