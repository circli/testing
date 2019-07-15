<?php declare(strict_types=1);

namespace Circli\Testing\Endpoint;

use PHPUnit\Framework\TestCase;

abstract class AbstractEndpointTest extends TestCase
{
	protected static $tmpFolder = '/tmp';
	protected static $serverHost;
	protected static $serverPort;
	protected static $initScript;

	private static $pid;
	private static $db;

	public static function setUpBeforeClass(): void
	{
		/** @noinspection PhpUndefinedConstantInspection */
		self::$serverHost = WEB_SERVER_HOST;
		/** @noinspection PhpUndefinedConstantInspection */
		self::$serverPort = WEB_SERVER_PORT;
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

		if (self::$initScript) {
			// Command that starts the built-in web server
			$command = sprintf(
				"/bin/bash -c '%s %s %d %s %s %s > /dev/null 2>&1 &'",
				$script,
				self::$serverHost,
				self::$serverPort,
				self::$tmpFolder,
				$db,
				self::$initScript
			);
		}
		else {
			// Command that starts the built-in web server
			$command = sprintf(
				"/bin/bash -c '%s %s %d %s %s > /dev/null 2>&1 &'",
				$script,
				self::$serverHost,
				self::$serverPort,
				self::$tmpFolder,
				$db
			);
		}

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
