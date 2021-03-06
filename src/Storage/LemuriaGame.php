<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Storage;

use JetBrains\PhpStorm\ArrayShape;

use Lemuria\Model\Fantasya\Storage\JsonGame;
use Lemuria\Model\Fantasya\Storage\JsonProvider;

class LemuriaGame extends JsonGame
{
	private const GAME_DIR = 'game';

	private const STRINGS_DIR = __DIR__ . '/../../resources';

	private const STRINGS_FILE = 'strings.json';

	public function __construct(protected LemuriaConfig $config) {
		parent::__construct();
	}

	/**
	 * @return array(string=>string)
	 */
	protected function getLoadStorage(): array {
		$round   = $this->config[LemuriaConfig::ROUND];
		$path    = $this->config->getStoragePath() . DIRECTORY_SEPARATOR . self::GAME_DIR . DIRECTORY_SEPARATOR . $round;
		$storage = [JsonProvider::DEFAULT => new JsonProvider($path)];
		return $this->addStringsStorage($storage);
	}

	/**
	 * @return array(string=>string)
	 */
	#[ArrayShape([JsonProvider::DEFAULT => '\Lemuria\Model\Fantasya\Storage\JsonProvider'])]
	protected function getSaveStorage(): array {
		$round = $this->config[LemuriaConfig::ROUND] + 1;
		$path  = $this->config->getStoragePath() . DIRECTORY_SEPARATOR . self::GAME_DIR . DIRECTORY_SEPARATOR . $round;
		return [JsonProvider::DEFAULT => new JsonProvider($path)];
	}

	/**
	 * @return array(string=>string)
	 */
	protected function addStringsStorage(array $storage): array {
		$storage[self::STRINGS_FILE] = new JsonProvider(self::STRINGS_DIR);
		return $storage;
	}
}
