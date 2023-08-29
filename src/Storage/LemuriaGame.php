<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Storage;

use Lemuria\Engine\Fantasya\Storage\Migration\AbstractUpgrade;
use Lemuria\Model\Fantasya\Storage\JsonGame;
use Lemuria\Model\Fantasya\Storage\JsonProvider;
use Lemuria\Storage\Provider;
use Lemuria\Storage\RecursiveProvider;

class LemuriaGame extends JsonGame
{
	protected const GAME_DIR = 'game';

	private const STRINGS_DIR = __DIR__ . '/../../resources';

	private const STRINGS_FILE = 'strings.json';

	private const SCRIPTS_DIR = 'scripts';

	public function __construct(protected readonly LemuriaConfig $config) {
		parent::__construct();
	}

	/**
	 * Get NPC scripts data.
	 */
	public function getScripts(): array {
		$data       = [];
		$scriptsDir = self::GAME_DIR . DIRECTORY_SEPARATOR . self::SCRIPTS_DIR;
		$pathPos    = strlen($scriptsDir) + 1;
		$provider   = new RecursiveProvider($scriptsDir);
		foreach ($provider->glob() as $path) {
			$file        = substr($path, $pathPos);
			$data[$file] = $provider->read($file);
		}
		return $data;
	}

	/**
	 * Set NPC scripts data.
	 */
	public function setScripts(array $scripts): static {
		$scriptsDir = self::GAME_DIR . DIRECTORY_SEPARATOR . self::SCRIPTS_DIR;
		$provider   = new RecursiveProvider($scriptsDir);
		foreach ($scripts as $file => $data) {
			$provider->write($file, $data);
		}
		return $this;
	}

	public function migrate(): static {
		$calendar = $this->getCalendar();
		$version  = $calendar['version'];
		parent::migrate();
		foreach (AbstractUpgrade::getAll() as $class) {
			/** @var AbstractUpgrade $upgrade */
			$upgrade = new $class($this);
			if ($upgrade->isPending($version)) {
				$upgrade->upgrade();
			}
		}
		return $this;
	}

	/**
	 * @return array<string, string>
	 */
	protected function getLoadStorage(): array {
		$round = $this->config[LemuriaConfig::ROUND];
		$path  = $this->config->getStoragePath() . DIRECTORY_SEPARATOR . self::GAME_DIR . DIRECTORY_SEPARATOR . $round;
		return $this->addStringsStorage([Provider::DEFAULT => new JsonProvider($path)]);
	}

	/**
	 * @return array<string, string>
	 */
	protected function getSaveStorage(): array {
		$round = $this->config[LemuriaConfig::ROUND] + 1;
		$path  = $this->config->getStoragePath() . DIRECTORY_SEPARATOR . self::GAME_DIR . DIRECTORY_SEPARATOR . $round;
		return [Provider::DEFAULT => new JsonProvider($path)];
	}

	/**
	 * @return array<string, string>
	 */
	protected function addStringsStorage(array $storage): array {
		$storage[self::STRINGS_FILE] = new JsonProvider(self::STRINGS_DIR);
		return $storage;
	}
}
