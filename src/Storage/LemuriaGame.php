<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Storage;

use Lemuria\Engine\Fantasya\Storage\Migration\AbstractUpgrade;
use Lemuria\Model\Fantasya\Storage\JsonGame;
use Lemuria\Model\Fantasya\Storage\JsonProvider;
use Lemuria\Storage\Provider;

class LemuriaGame extends JsonGame
{
	protected const string GAME_DIR = 'game';

	private const string STRINGS_DIR = __DIR__ . '/../../resources';

	private const string STRINGS_FILE = 'strings.json';

	public function __construct(protected readonly LemuriaConfig $config) {
		parent::__construct();
	}

	public function getScripts(): array {
		return [];
	}

	public function getQuests(): array {
		return [];
	}

	public function setScripts(array $scripts): static {
		return $this;
	}

	public function setQuests(array $quests): static {
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
