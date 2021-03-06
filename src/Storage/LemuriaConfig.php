<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Storage;

use JetBrains\PhpStorm\Pure;

use Lemuria\Engine\Debut;
use Lemuria\Engine\Fantasya\LemuriaDebut;
use Lemuria\Engine\Fantasya\LemuriaLog;
use Lemuria\Engine\Fantasya\LemuriaOrders;
use Lemuria\Engine\Fantasya\LemuriaReport;
use Lemuria\Engine\Fantasya\LemuriaScore;
use Lemuria\Engine\Fantasya\SingletonCatalog as EngineSingletonCatalog;
use Lemuria\Engine\Orders;
use Lemuria\Engine\Report;
use Lemuria\Engine\Score;
use Lemuria\Exception\LemuriaException;
use Lemuria\Factory\DefaultBuilder;
use Lemuria\Log;
use Lemuria\Model\Builder;
use Lemuria\Model\Calendar;
use Lemuria\Model\Catalog;
use Lemuria\Model\Config;
use Lemuria\Model\Calendar\BaseCalendar;
use Lemuria\Model\Game;
use Lemuria\Model\Fantasya\Exception\JsonException;
use Lemuria\Model\Fantasya\Factory\LemuriaCatalog;
use Lemuria\Model\Fantasya\Factory\LemuriaRegistry;
use Lemuria\Model\Fantasya\SingletonCatalog as ModelSingletonCatalog;
use Lemuria\Model\Fantasya\Storage\JsonProvider;
use Lemuria\Model\World;
use Lemuria\Model\World\HexagonalMap;
use Lemuria\Registry;

class LemuriaConfig implements \ArrayAccess, Config
{
	public const ROUND = 'round';

	public const MDD = 'mdd';

	public const CONFIG_FILE = 'config.json';

	public const LOG_DIR = 'log';

	public const LOG_FILE = 'lemuria.log';

	private const DEFAULTS = [
		self::ROUND => 0,
		self::MDD   => 0
	];

	protected array $defaults;

	private bool $hasChanged = false;

	private JsonProvider $file;

	private ?array $config;

	/**
	 * @throws JsonException
	 */
	public function __construct(private string $storagePath) {
		$this->initDefaults();
		$this->file = new JsonProvider($storagePath);
		if ($this->file->exists(self::CONFIG_FILE)) {
			$this->config = $this->file->read(self::CONFIG_FILE) + $this->defaults;
		}
	}

	/**
	 * @throws JsonException
	 */
	function __destruct() {
		if ($this->hasChanged) {
			$this->file->write(self::CONFIG_FILE, $this->config);
		}
	}

	/**
	 * @param string $offset
	 * @return bool
	 */
	public function offsetExists(mixed $offset): bool {
		return isset($this->config[$offset]);
	}

	public function offsetGet(mixed $offset): mixed {
		if (!$this->offsetExists($offset)) {
			throw new LemuriaException("No config value for '" . $offset ."'.");
		}
		return $this->config[$offset];
	}

	public function offsetSet(mixed $offset, mixed $value) {
		if (!$this->offsetExists($offset)) {
			throw new LemuriaException("Invalid config setting '" . $offset . "'.");
		}
		$this->config[$offset] = $value;
		$this->hasChanged      = true;
	}

	public function offsetUnset(mixed $offset): void {
		if (!$this->offsetExists($offset)) {
			throw new LemuriaException("No config value for '" . $offset ."'.");
		}
		$this->config[$offset] = $this->defaults[$offset];
		$this->hasChanged      = true;
	}

	public function Builder(): Builder {
		$builder = new DefaultBuilder();
		return $builder->register(new ModelSingletonCatalog())->register(new EngineSingletonCatalog());
	}

	public function Catalog(): Catalog {
		return new LemuriaCatalog();
	}

	#[Pure] public function Calendar(): Calendar {
		return new BaseCalendar();
	}

	#[Pure] public function Debut(): Debut {
		return new LemuriaDebut();
	}

	public function Game(): Game {
		return new LemuriaGame($this);
	}

	public function Orders(): Orders {
		return new LemuriaOrders();
	}

	public function Report(): Report {
		return new LemuriaReport();
	}

	#[Pure] public function World(): World {
		return new HexagonalMap();
	}

	public function Score(): Score {
		return new LemuriaScore();
	}

	#[Pure] public function Registry(): Registry {
		return new LemuriaRegistry();
	}

	/**
	 * @throws \Exception
	 */
	#[Pure] public function Log(): Log {
		return $this->createLog($this->storagePath . DIRECTORY_SEPARATOR . self::LOG_DIR . DIRECTORY_SEPARATOR . self::LOG_FILE);
	}

	public function getStoragePath(): string {
		return $this->storagePath;
	}

	protected function initDefaults(): void {
		$this->defaults = self::DEFAULTS;
	}

	#[Pure] protected function createLog(string $logPath): Log {
		return new LemuriaLog($logPath);
	}
}
