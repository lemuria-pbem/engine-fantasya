<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya;

use Monolog\ErrorHandler;
use Monolog\Handler\HandlerInterface;
use Monolog\Handler\NullHandler;
use Monolog\Handler\StreamHandler;
use Monolog\Level;
use Monolog\Logger;
use Psr\Log\LoggerInterface;

use Lemuria\Log;

class LemuriaLog implements Log
{
	protected ?Level $consoleLevel = null;

	protected ?Level $fileLevel = null;

	public function __construct(protected readonly string $logPath, protected readonly bool $addErrorHandler = true) {
	}

	public function getLogger(): LoggerInterface {
		$log = new Logger('lemuria');
		foreach ($this->getHandlers() as $handler) {
			$log->pushHandler($handler);
		}
		if ($this->addErrorHandler) {
			ErrorHandler::register($log);
		}
		return $log;
	}

	/**
	 * @return array<HandlerInterface>
	 */
	protected function getHandlers(): array {
		$handlers = [];
		if ($this->consoleLevel) {
			$handlers[] = $this->createConsoleHandler();
		}
		if ($this->fileLevel) {
			$handlers[] = $this->createFileHandler();
		}
		if (empty($handlers)) {
			$handlers[] = new NullHandler(Level::Emergency);
		}
		return $handlers;
	}

	protected function createConsoleHandler(): HandlerInterface {
		return new StreamHandler('php://stdout', $this->consoleLevel);
	}

	protected function createFileHandler(): HandlerInterface {
		$logDir = dirname($this->logPath);
		if (!file_exists($logDir)) {
			@mkdir($logDir, 0775, true);
		}
		file_exists($this->logPath) ? file_put_contents($this->logPath, '') : touch($this->logPath);
		return new StreamHandler($this->logPath, $this->fileLevel);
	}
}
