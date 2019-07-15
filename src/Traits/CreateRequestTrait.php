<?php declare(strict_types=1);

namespace Circli\Testing\Traits;

use PHPUnit\Framework\MockObject\MockObject;
use Psr\Http\Message\ServerRequestInterface;

trait CreateRequestTrait
{
	use LoadResourceTrait;

	/**
	 * Create mock request
	 *
	 * @param array $attributes
	 * @param array|string $payload
	 * @return ServerRequestInterface|MockObject
	 */
	public function getMockRequest(array $attributes, $payload = [])
	{
		$request = $this->createMock(ServerRequestInterface::class);

		if (\is_string($payload)) {
			if (substr($payload, -7) === 'request') {
				[$payload, $attributes] = $this->getLoader()->loadRequest($payload);
			}
			else {
				$payload = $this->loadResource($payload);
			}
		}

		$request->method('getParsedBody')->willReturn($payload);

		$returnMap = [];
		foreach ($attributes as $key => $value) {
			$returnMap[$key] = $value;
		}

		$request->method('getAttribute')->willReturnCallback(static function ($a) use ($returnMap) {
			return $returnMap[$a] ?? null;
		});

		return $request;
	}
}
