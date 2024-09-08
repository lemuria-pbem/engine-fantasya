<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Storage;

use Lemuria\Engine\Debut;
use Lemuria\Engine\Fantasya\Factory\LemuriaCatalog;
use Lemuria\Engine\Fantasya\Factory\Namer\DefaultNamer;
use Lemuria\Engine\Fantasya\LemuriaDebut;
use Lemuria\Engine\Fantasya\LemuriaHostilities;
use Lemuria\Engine\Fantasya\LemuriaLog;
use Lemuria\Engine\Fantasya\LemuriaOrders;
use Lemuria\Engine\Fantasya\LemuriaReport;
use Lemuria\Engine\Fantasya\LemuriaScore;
use Lemuria\Engine\Fantasya\SingletonCatalog as EngineSingletonCatalog;
use Lemuria\Engine\Hostilities;
use Lemuria\Engine\Orders;
use Lemuria\Engine\Report;
use Lemuria\Engine\Score;
use Lemuria\Exception\LemuriaException;
use Lemuria\Factory\DefaultBuilder;
use Lemuria\Factory\Namer;
use Lemuria\FeatureFlag;
use Lemuria\Log;
use Lemuria\Model\Builder;
use Lemuria\Model\Calendar;
use Lemuria\Model\Catalog;
use Lemuria\Model\Config;
use Lemuria\Model\Calendar\BaseCalendar;
use Lemuria\Model\Game;
use Lemuria\Model\Fantasya\Exception\JsonException;
use Lemuria\Model\Fantasya\Factory\LemuriaRegistry;
use Lemuria\Model\Fantasya\SingletonCatalog as ModelSingletonCatalog;
use Lemuria\Model\Fantasya\Storage\JsonProvider;
use Lemuria\Model\World;
use Lemuria\Model\World\HexagonalMap;
use Lemuria\Registry;
use Lemuria\Scenario\Scripts;

abstract class LemuriaConfig implements \ArrayAccess, Config
{
	public final const string ROUND = 'round';

	public final const string MDD = 'mdd';

	public final const string LOCALE = 'locale';

	public final const string CONFIG_FILE = 'config.json';

	public final const string LOG_DIR = 'log';

	public final const string LOG_FILE = 'lemuria.log';

	/**
	 * @type array<string, int>
	 */
	private const array DEFAULTS = [
		self::ROUND  => 0,
		self::MDD    => 0,
		self::LOCALE => ''
	];

	protected array $defaults;

	protected FeatureFlag $featureFlag;

	private string $logFile = self::LOG_FILE;

	private bool $hasChanged = false;

	private readonly JsonProvider $file;

	private ?array $config;

	/**
	 * @throws JsonException
	 */
	public function __construct(private readonly string $storagePath) {
		$this->featureFlag = new FeatureFlag();
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

	public function offsetSet(mixed $offset, mixed $value): void {
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

	public function Locale(): string {
		return $this->config[self::LOCALE];
	}

	public function Builder(): Builder {
		$builder = new DefaultBuilder();
		return $builder->register(new ModelSingletonCatalog())->register(new EngineSingletonCatalog());
	}

	public function Catalog(): Catalog {
		return new LemuriaCatalog();
	}

	public function Calendar(): Calendar {
		return new BaseCalendar();
	}

	public function Debut(): Debut {
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

	public function World(): World {
		return new HexagonalMap();
	}

	public function Score(): Score {
		return new LemuriaScore();
	}

	public function Hostilities(): Hostilities {
		return new LemuriaHostilities();
	}

	public function Registry(): Registry {
		return new LemuriaRegistry();
	}

	/**
	 * @throws \Exception
	 */
	public function Log(): Log {
		return $this->createLog($this->storagePath . DIRECTORY_SEPARATOR . self::LOG_DIR . DIRECTORY_SEPARATOR . $this->logFile);
	}

	public function FeatureFlag(): FeatureFlag {
		return $this->featureFlag;
	}

	public function Namer(): Namer {
		return new DefaultNamer();
	}

	public function Scripts(): ?Scripts {
		return null;
	}

	public function getStoragePath(): string {
		return $this->storagePath;
	}

	public function setLogFile(string $fileName): LemuriaConfig {
		$this->logFile = $fileName;
		return $this;
	}

	protected function initDefaults(): void {
		$this->defaults = self::DEFAULTS;
	}

	protected function createLog(string $logPath): Log {
		return new LemuriaLog($logPath);
	}
}
