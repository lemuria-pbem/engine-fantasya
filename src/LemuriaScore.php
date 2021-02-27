<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Lemuria;

use Lemuria\Engine\Lemuria\Factory\EffectFactory;
use Lemuria\Engine\Score;
use Lemuria\Exception\LemuriaException;
use Lemuria\Identifiable;
use Lemuria\Lemuria;
use Lemuria\SerializableTrait;
use function Lemuria\getClass;

class LemuriaScore implements Score
{
	use SerializableTrait;

	private EffectFactory $factory;

	/**
	 * @var array(int=>array)
	 */
	private array $effects = [];

	private bool $isLoaded = false;

	/**
	 * Init the score.
	 */
	public function __construct() {
		$reflection = new \ReflectionClass(Score::class);
		foreach ($reflection->getConstants() as $namespace) {
			if (!is_int($namespace)) {
				throw new LemuriaException('Expected integer score namespace.');
			}
			$this->effects[$namespace] = [];
		}
		$this->factory = new EffectFactory(State::getInstance());
	}

	/**
	 * Search for an existing Effect.
	 */
	public function find(Identifiable $effect): Effect {
		if ($effect instanceof Effect) {
			$namespace = $effect->Catalog();
			$id        = $effect->Id()->Id();
			$class     = getClass($effect);
			return $this->effects[$namespace][$id][$class] ?? $effect;
		}
		throw new LemuriaException('Expected instance of Effect.');
	}

	/**
	 * Add an Effect to persistence.
	 */
	public function add(Identifiable $effect): Score {
		$namespace = $effect->Catalog();
		$id        = $effect->Id()->Id();
		$class     = getClass($effect);
		$this->effects[$namespace][$id][$class] = $effect;
		return $this;
	}

	/**
	 * Remove an Effect from persistence.
	 */
	public function remove(Identifiable $effect): Score {
		$namespace = $effect->Catalog();
		$id        = $effect->Id()->Id();
		$class     = getClass($effect);
		unset($this->effects[$namespace][$id][$class]);
		return $this;
	}

	/**
	 * Load message data into score.
	 */
	public function load(): Score {
		if (!$this->isLoaded) {
			$effects = Lemuria::Game()->getEffects();
			foreach ($effects as $data) {
				$this->add($this->factory->create($data));
			}
			$this->isLoaded = true;
		}
		return $this;
	}

	/**
	 * Save game data from score.
	 */
	public function save(): Score {
		$effects = [];
		foreach ($this->effects as $namespace) {
			foreach ($namespace as $id) {
				foreach ($id as $effect) {
					$effects[] = $effect->serialize();
				}
			}
		}
		Lemuria::Game()->setEffects($effects);
		return $this;
	}
}
