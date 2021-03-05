<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Lemuria\Storage;

use JetBrains\PhpStorm\Pure;

use Lemuria\Engine\Lemuria\LemuriaOrders;
use Lemuria\Engine\Lemuria\LemuriaReport;
use Lemuria\Engine\Lemuria\LemuriaScore;
use Lemuria\Engine\Lemuria\SingletonCatalog as EngineSingletonCatalog;
use Lemuria\Engine\Orders;
use Lemuria\Engine\Report;
use Lemuria\Engine\Score;
use Lemuria\Exception\LemuriaException;
use Lemuria\Factory\DefaultBuilder;
use Lemuria\Model\Builder;
use Lemuria\Model\Calendar;
use Lemuria\Model\Catalog;
use Lemuria\Model\Config;
use Lemuria\Model\Calendar\BaseCalendar;
use Lemuria\Model\Game;
use Lemuria\Model\Lemuria\Exception\JsonException;
use Lemuria\Model\Lemuria\Factory\LemuriaCatalog;
use Lemuria\Model\Lemuria\Factory\LemuriaRegistry;
use Lemuria\Model\Lemuria\SingletonCatalog as ModelSingletonCatalog;
use Lemuria\Model\Lemuria\Storage\JsonProvider;
use Lemuria\Model\World;
use Lemuria\Model\World\HexagonalMap;
use Lemuria\Registry;

class LemuriaConfig implements \ArrayAccess, Config
{
	public const ROUND = 'round';

	public const MDD = 'mdd';

	private const CONFIG_FILE = 'config.json';

	private const LOG_DIR = 'log';

	private const LOG_FILE = 'lemuria.log';

	private const DEFAULTS = [
		self::ROUND => 0,
		self::MDD   => 0
	];

	private JsonProvider $file;

	private ?array $config;

	/**
	 * @throws JsonException
	 */
	public function __construct(private string $storagePath) {
		$this->file = new JsonProvider($storagePath);
		if ($this->file->exists(self::CONFIG_FILE)) {
			$this->config = $this->file->read(self::CONFIG_FILE);
		} else {
			$this->config = self::DEFAULTS;
		}
	}

	/**
	 * @throws JsonException
	 */
	function __destruct() {
		$this->file->write(self::CONFIG_FILE, $this->config);
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
	}

	public function offsetUnset(mixed $offset): void {
		if (!$this->offsetExists($offset)) {
			throw new LemuriaException("No config value for '" . $offset ."'.");
		}
		$this->config[$offset] = self::DEFAULTS[$offset];
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

	public function getStoragePath(): string {
		return $this->storagePath;
	}

	#[Pure] public function getPathToLog(): string {
		return $this->storagePath . DIRECTORY_SEPARATOR . self::LOG_DIR . DIRECTORY_SEPARATOR . self::LOG_FILE;
	}
}
