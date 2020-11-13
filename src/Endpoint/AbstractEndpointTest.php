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

	private static $pid;
	private static $db;

	public static function setUpBeforeClass(): void
	{
		/** @noinspection PhpUndefinedConstantInspection */
		self::$serverHost = WEB_SERVER_HOST;
		/** @noinspection PhpUndefinedConstantInspection */
		self::$serverPort = WEB_SERVER_PORT;
		/** @noinspection PhpUndefinedConstantInspection */
		self::$serverRoot = WEB_SERVER_ROOT;
		self::startServer();
	}

	protected function setUp(): void
	{
		self::resetData();
	}

	private static function startServer(): void
	{
		if (self::$pid) {
			return;
		}

		$public = __DIR__ . '/public';
		$script = $public . '/start.sh';
		$pidFile = $public . '/.pid';

		$log = self::$tmpFolder . '/server.log';
		$db = 'server-' . $_SERVER['REQUEST_TIME'] . '.db';

		$commandFormat = '/bin/bash -c \'';
		$commandFormat .= '%s %s %s %d %s %s';
		$commandArgs = [
			$script,
			self::$serverRoot ?: '-',
			self::$serverHost,
			self::$serverPort,
			self::$tmpFolder,
			$db
		];

		if (self::$initScript) {
			$commandFormat .= ' %s';
			$commandArgs[] = self::$initScript;

			if (self::$shutdownScript) {
				$commandFormat .= ' %s';
				$commandArgs[] = self::$shutdownScript;
			}
		}

		$commandFormat .= ' > /dev/null 2>&1 &\'';

		$command = vsprintf($commandFormat, $commandArgs);
		// Execute the command and store the process ID
		exec($command, $output, $ret);
		// Need a little time to make sure the php server is up and running
		usleep(40000);

		$pid = \is_file($pidFile) ? (int)\file_get_contents($pidFile) : 0;
		if (!$pid || !\is_dir("/proc/$pid")) {
			throw new \RuntimeException("Could not start php server; See $log for details");
		}

		/** @noinspection ForgottenDebugOutputInspection */
		error_log(sprintf(
			'%s - Web server started on %s:%d with PID %d' . PHP_EOL,
			date('r'),
			self::$serverHost,
			self::$serverPort,
			$pid
		), 3, $log);

		self::$pid = $pid;
		self::$db = self::$tmpFolder . "/$db";

		register_shutdown_function(function () use ($pid, $log) {
			/** @noinspection ForgottenDebugOutputInspection */
			error_log(sprintf('%s - Killing process with ID %d' . PHP_EOL, date('r'), $pid), 3, $log);
			exec('kill ' . self::$pid . ' > /dev/null 2>&1');
		});
	}

	private static function resetData(): void
	{
		if (self::$db && \is_file(self::$db)) {
			unlink(self::$db);
		}
	}
}
