<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Storage;

use JetBrains\PhpStorm\ArrayShape;

use Lemuria\Model\Fantasya\Storage\JsonProvider;
use Lemuria\Storage\NullProvider;

class NewcomerGame extends LemuriaGame
{
	private const NEWCOMERS_FILE = 'newcomers.json';

	private JsonProvider $writeProvider;

	public function __construct(protected NewcomerConfig $config) {
		parent::__construct($config);
	}

	/**
	 * @return array(string=>string)
	 */
	#[ArrayShape([JsonProvider::DEFAULT => '\Lemuria\Storage\NullProvider', self::NEWCOMERS_FILE => '\Lemuria\Model\Fantasya\Storage\JsonProvider'])]
	protected function getSaveStorage(): array {
		$round               = $this->config[LemuriaConfig::ROUND];
		$path                = $this->config->getStoragePath() . DIRECTORY_SEPARATOR . self::GAME_DIR . DIRECTORY_SEPARATOR . $round;
		$this->writeProvider = new JsonProvider($path);
		return [JsonProvider::DEFAULT => new NullProvider(''), self::NEWCOMERS_FILE => $this->writeProvider];
	}
}
