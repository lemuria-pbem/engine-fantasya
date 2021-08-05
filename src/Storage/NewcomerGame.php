<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Storage;

use JetBrains\PhpStorm\ArrayShape;

use Lemuria\Exception\LemuriaException;
use Lemuria\Model\Fantasya\Storage\JsonProvider;
use Lemuria\Storage\NullProvider;
use Lemuria\Storage\Provider;

class NewcomerGame extends LemuriaGame
{
	private const NEWCOMERS_FILE = 'newcomers.json';

	public function __construct(NewcomerConfig $config) {
		parent::__construct($config);
	}

	/**
	 * @return array(string=>string)
	 */
	#[ArrayShape([Provider::DEFAULT => '\Lemuria\Storage\NullProvider', self::NEWCOMERS_FILE => '\Lemuria\Model\Fantasya\Storage\JsonProvider'])]
	protected function getSaveStorage(): array {
		$round = $this->config[LemuriaConfig::ROUND];
		$path  = $this->config->getStoragePath() . DIRECTORY_SEPARATOR . self::GAME_DIR . DIRECTORY_SEPARATOR . $round;
		return [Provider::DEFAULT => new NullProvider(''), self::NEWCOMERS_FILE => new JsonProvider($path)];
	}

	protected function checkProvider(Provider $provider): Provider {
		if ($provider instanceof JsonProvider) {
			return $provider;
		}
		if ($provider instanceof NullProvider) {
			return $provider;
		}
		throw new LemuriaException('JsonProvider or NullProvider required.');
	}
}
