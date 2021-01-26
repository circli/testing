<?php declare(strict_types=1);

namespace Circli\Testing\Endpoint;

final class Server
{
	private $pid;

	private $host;

	private $port;

	private $tmpFolder;

	private $root;

	private $initScript;

	private $shutdownScript;

	private $db;

	private $log;

	private $haveDumpedLog = false;

	public function __construct(
		string $host,
		int $port,
		string $tmpFolder,
		?string $root,
		?string $initScript,
		?string $shutdownScript
	) {
		$this->host = $host;
		$this->port = $port;
		$this->tmpFolder = $tmpFolder;
		$this->root = $root;
		$this->initScript = $initScript;
		$this->shutdownScript = $shutdownScript;
	}

	public function start()
	{
		if ($this->pid) {
			return;
		}

		$public = __DIR__ . '/public';
		$script = $public . '/start.sh';
		$rootHash = substr(md5($this->root . PHP_EOL), 0, 8);
		$pidFile = $public . '/' . $rootHash . '.pid';

		if (exec('ps aux | grep "'.$script.'" | grep -v "grep " | grep "' . $this->root . '"')) {
			return;
		}

		$this->log = $this->tmpFolder . '/server.' . $rootHash . '.log';
		$db = 'server-' . $_SERVER['REQUEST_TIME'] . '.db';

		$commandFormat = '/bin/bash -c \'';
		$commandFormat .= '%s %s %s %d %s %s';
		$commandArgs = [
			$script,
			$this->root ?: '-',
			$this->host,
			$this->port,
			$this->tmpFolder,
			$db
		];

		if ($this->initScript) {
			$commandFormat .= ' %s';
			$commandArgs[] = $this->initScript;

			if ($this->shutdownScript) {
				$commandFormat .= ' %s';
				$commandArgs[] = $this->shutdownScript;
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
			throw new \RuntimeException("Could not start php server; See {$this->log} for details");
		}

		/** @noinspection ForgottenDebugOutputInspection */
		error_log(sprintf(
			'%s - Web server started on %s:%d with PID %d' . PHP_EOL,
			date('r'),
			$this->host,
			$this->port,
			$pid
		), 3, $this->log);

		$this->pid = $pid;
		$this->db = $this->tmpFolder . "/$db";

		register_shutdown_function(function () {
			/** @noinspection ForgottenDebugOutputInspection */
			error_log(sprintf('%s - Killing process with ID %d' . PHP_EOL, date('r'), $this->pid), 3, $this->log);
			exec('kill ' . $this->pid . ' > /dev/null 2>&1');
		});

	}

	public function getDb()
	{
		return $this->db;
	}

	public function getLog()
	{
		return $this->log;
	}

	public function getHost(): string
	{
		return $this->host;
	}

	public function dumpLog(): void
	{
		if ($this->haveDumpedLog) {
			return;
		}
		echo 'Logs for: ' . $this->getHost() . "\n\n";
		echo file_get_contents($this->getLog());
		echo "\n\n---------------\n\n";
		$this->haveDumpedLog = true;
	}

	public function haveDumpedLog(): bool
	{
		return $this->haveDumpedLog;
	}

	public function getRoot(): ?string
	{
		return $this->root;
	}

	public function stop()
	{
		/** @noinspection ForgottenDebugOutputInspection */
		error_log(sprintf('%s - Killing process with ID %d' . PHP_EOL, date('r'), $this->pid), 3, $this->log);
		exec('kill ' . $this->pid . ' > /dev/null 2>&1');
	}
}
