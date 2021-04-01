<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Storage;

use JetBrains\PhpStorm\ArrayShape;

use Lemuria\Model\Exception\ModelException;
use Lemuria\Model\Fantasya\Storage\JsonGame;
use Lemuria\Model\Fantasya\Storage\JsonProvider;

class LemuriaGame extends JsonGame
{
	private const GAME_DIR = 'game';

	private const STRINGS_DIR = __DIR__ . '/../../resources';

	private const STRINGS_FILE = 'strings.json';

	private JsonProvider $readProvider;

	private JsonProvider $writeProvider;

	public function __construct(protected LemuriaConfig $config) {
		parent::__construct();
	}

	public function getNewcomers(): array {
		return $this->readProvider->read('newcomers.json');
	}

	public function setNewcomers(array $newcomers): LemuriaGame {
		if (!ksort($newcomers)) {
			throw new ModelException('Sorting constructions failed.');
		}
		$this->writeProvider->write('newcomers.json', array_values($newcomers));
		return $this;
	}

	/**
	 * @return array(string=>string)
	 */
	protected function getLoadStorage(): array {
		$round              = $this->config[LemuriaConfig::ROUND];
		$path               = $this->config->getStoragePath() . DIRECTORY_SEPARATOR . self::GAME_DIR . DIRECTORY_SEPARATOR . $round;
		$this->readProvider = new JsonProvider($path);
		return $this->addStringsStorage([JsonProvider::DEFAULT => $this->readProvider]);
	}

	/**
	 * @return array(string=>string)
	 */
	#[ArrayShape([JsonProvider::DEFAULT => '\Lemuria\Model\Fantasya\Storage\JsonProvider'])]
	protected function getSaveStorage(): array {
		$round               = $this->config[LemuriaConfig::ROUND] + 1;
		$path                = $this->config->getStoragePath() . DIRECTORY_SEPARATOR . self::GAME_DIR . DIRECTORY_SEPARATOR . $round;
		$this->writeProvider = new JsonProvider($path);
		return [JsonProvider::DEFAULT => $this->writeProvider];
	}

	/**
	 * @return array(string=>string)
	 */
	protected function addStringsStorage(array $storage): array {
		$storage[self::STRINGS_FILE] = new JsonProvider(self::STRINGS_DIR);
		return $storage;
	}
}
