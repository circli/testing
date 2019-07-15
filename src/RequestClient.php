<?php declare(strict_types=1);

namespace Circli\Testing;

interface RequestClient
{
	public const GET = 'get';
	public const PUT = 'put';
	public const POST = 'post';
	public const DELETE = 'delete';

	public function doRequest(string $method, string $endpoint, array $payload);

	public function getResponseCode(): int;
}
