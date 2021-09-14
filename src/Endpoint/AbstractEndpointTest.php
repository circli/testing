<?php declare(strict_types=1);

namespace Circli\Testing\Endpoint;

use PHPUnit\Framework\TestCase;

abstract class AbstractEndpointTest extends TestCase
{
	protected static $tmpFolder = '/tmp';
	protected static $serverHost;
	protected static $serverPort;
	protected static $serverRoot;
	protected static $initScript;
	protected static $shutdownScript;
	protected static $waitTime;

	/** @var Server[] */
	protected static $servers = [];
	protected static $currentServer;

	public static function setUpBeforeClass(): void
	{
		/** @noinspection PhpUndefinedConstantInspection */
		self::$serverHost = WEB_SERVER_HOST;
		/** @noinspection PhpUndefinedConstantInspection */
		self::$serverPort = WEB_SERVER_PORT;
		if (!self::$serverRoot && defined('WEB_SERVER_ROOT')) {
			self::$serverRoot = WEB_SERVER_ROOT;
		}
		self::startServer();
	}

	protected function setUp(): void
	{
		self::resetData();
	}

	private static function startServer(): void
	{
		if (self::$servers) {
			return;
		}

		$server = new Server(
			self::$serverHost,
			(int)self::$serverPort,
			self::$tmpFolder,
			self::$serverRoot ?: '-',
			self::$initScript,
			self::$shutdownScript
		);
		if (self::$waitTime) {
			$server->waitTime = self::$waitTime;
		}

		$server->start();
		self::$servers['auto'] = $server;
		self::$currentServer = 'auto';
	}

	private static function resetData(): void
	{
		if (isset(self::$servers['auto']) &&
			self::$servers['auto']->getDb() &&
			\is_file(self::$servers['auto']->getDb())
		) {
			unlink(self::$servers['auto']->getDb());
		}
	}
}
