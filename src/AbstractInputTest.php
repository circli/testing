<?php declare(strict_types=1);

namespace Circli\Testing;

use PHPUnit\Framework\TestCase;
use Circli\Testing\Traits\CreateRequestTrait;

abstract class AbstractInputTest extends TestCase
{
	protected $inputs;
	protected $filter;

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

		$filter = null;
		if (isset($this->filter) && is_string($this->filter)) {
			$filter = function ($file) {
				return preg_match($this->filter, $file) !== 0;
			};
		}

		$inputs = [];
		foreach ($this->inputs as $cls => $data) {
			$attributes = [];
			if (is_array($data)) {
				if (is_array($data[1])) {
					$attributes = $data[1];
					$data = $data[0];
				}
			}
			if (is_string($data)) {
				$files = $this->getLoader()->getFiles($data, $filter);
				foreach ($files as $file) {
					$inputs[] = [
						$cls,
						$file,
						$attributes,
					];
				}
			}
			else {
				$inputs[] = [
					$cls,
					$data[0],
					$attributes,
				];
			}
		}

		return $inputs;
	}
}
