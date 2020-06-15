<?php declare(strict_types=1);

namespace Circli\Testing;

use PHPUnit\Framework\TestCase;
use Symfony\Component\Process\Process;

abstract class AbstractConsoleTest extends TestCase
{
	protected static $initScript;
	protected static $shutdownScript;

	public function runProcess(Process $process): Process
	{
		$env = [
			'APP_ENV' => 'testing',
		];
		if (self::$initScript && file_exists(self::$initScript)) {
			Process::fromShellCommandline('php ' . self::$initScript, null, $env);
		}

		$process->run(null, $env);

		if (self::$shutdownScript && file_exists(self::$shutdownScript)) {
			Process::fromShellCommandline('php ' . self::$shutdownScript, null, $env);
		}
		return $process;
	}

	public function runCommand(string $command, array $args): Process
	{
		array_unshift($args, $command);
		return $this->runProcess(new Process($args));
	}
}
