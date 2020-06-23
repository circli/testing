<?php declare(strict_types=1);

namespace Circli\Testing;

use PHPUnit\Framework\TestCase;
use Symfony\Component\Process\Process;

abstract class AbstractConsoleTest extends TestCase
{
	protected static $initScript;
	protected static $shutdownScript;

	protected $beforeShutdown;

	public function runProcess(Process $process): Process
	{
		$env = [
			'APP_ENV' => 'testing',
		];
		if (self::$initScript && file_exists(self::$initScript)) {
			Process::fromShellCommandline('php ' . self::$initScript, null, $env)->run();
		}
		$this->afterInit();

		$process->run(null, $env);

		$this->beforeShutdown();
		if (self::$shutdownScript && file_exists(self::$shutdownScript)) {
			Process::fromShellCommandline('php ' . self::$shutdownScript, null, $env)->run();
		}
		return $process;
	}

	public function runConsoleCommand(array $args): Process
	{
		array_unshift($args, $this->findConsole());
		return $this->runProcess(new Process($args));
	}

	protected function findConsole(): string
	{
		$maxDepth = 6;
		while($maxDepth) {
			$base = dirname(__DIR__, $maxDepth);
			if (file_exists($base . '/vendor/bin/console')) {
				return $base . '/vendor/bin/console';
			}
			$maxDepth--;
		}
		throw new \RuntimeException('console command not found');
	}

	protected function afterInit(): void
	{

	}

	protected function beforeShutdown(): void
	{
		if ($this->beforeShutdown && is_callable($this->beforeShutdown)) {
			$callback = $this->beforeShutdown;
			$callback();
		}
	}
}
