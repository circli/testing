<?php declare(strict_types=1);

namespace Circli\Testing\Endpoint;

use Circli\Testing\Classifier;
use Circli\Testing\HttpStatusClassifier;
use Circli\Testing\RequestClient;
use Circli\Testing\Traits\LoadResourceTrait;
use Circli\Testing\Traits\ModuleLoaderTrait;

abstract class AbstractAutoEndpointTest extends AbstractEndpointTest
{
	use LoadResourceTrait;
	use ModuleLoaderTrait;

	protected $autoInputs;
	protected $autoEndpoint;
	protected $autoMethod;
	protected $apiUrl;

	protected function setUp(): void
	{
		parent::setUp();
		$this->apiUrl = 'http://' . self::$serverHost . ':' . self::$serverPort;
	}

	protected function setupAutoEndpoint(string $method, string $endpoint): void
	{
		$this->autoMethod = $method;
		$this->autoEndpoint = $endpoint;
	}

	/**
	 * @dataProvider inputs
	 */
	public function testAll(string $file, int $returnStatus): void
	{
		if (!$this->autoEndpoint) {
			throw new \RuntimeException('You need to run setupAutoEndpoint first');
		}

		$client = $this->createClient();

		$this->preAutoRequest($client, $file);
		$response = $client->doRequest(
			$this->autoMethod,
			$this->autoEndpoint,
			$this->loadResource($file)
		);

		$this->assertSame($returnStatus, $client->getResponseCode());
		$this->assertResponseStructure($response, $client);
	}

	public function assertResponseStructure($response, RequestClient $client)
	{
		$this->assertTrue(true);
	}

	public function inputs(): array
	{
		if (!$this->autoInputs) {
			// slash means run all in root folder
			$this->autoInputs = '/';
		}

		$resources = $this->getLoader()->getFiles($this->autoInputs);
		$classifier = $this->getClassifier();

		$inputs = [];
		foreach ($resources as $i => $resource) {
			$classifier->classify($resource);
			$inputs[] = [
				$resource,
				$classifier->expectedStatusCode(),
			];
		}

		return $inputs;
	}

	protected function preAutoRequest(RequestClient $client, string $file = null): void
	{
	}

	public function getClassifier(): Classifier
	{
		return new HttpStatusClassifier();
	}

	abstract protected function createClient(): RequestClient;
}
