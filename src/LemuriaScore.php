<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Lemuria;

use Lemuria\Engine\Lemuria\Factory\EffectFactory;
use Lemuria\Engine\Score;
use Lemuria\Lemuria;
use Lemuria\SerializableTrait;

class LemuriaScore implements Score
{
	use SerializableTrait;

	private EffectFactory $factory;

	private array $effects = [];

	private bool $isLoaded = false;

	public function __construct() {
		$this->factory = new EffectFactory(State::getInstance());
	}

	/**
	 * Load message data into report.
	 */
	public function load(): Score {
		if (!$this->isLoaded) {
			$effects = Lemuria::Game()->getEffects();
			foreach ($effects as $data) {
				$this->effects[] = $this->factory->create($data);
			}
			$this->isLoaded = true;
		}
		return $this;
	}

	/**
	 * Save game data from report.
	 */
	public function save(): Score {
		$effects = [];
		foreach ($this->effects as $effect /* @var Effect $effect */) {
			$effects[] = $effect->serialize();
		}
		Lemuria::Game()->setEffects($effects);
		return $this;
	}
}
