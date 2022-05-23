<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya;

use function Lemuria\getClass;
use Lemuria\Engine\Fantasya\Factory\EffectFactory;
use Lemuria\Engine\Score;
use Lemuria\Exception\LemuriaException;
use Lemuria\Identifiable;
use Lemuria\Lemuria;
use Lemuria\SerializableTrait;

class LemuriaScore implements Score
{
	use SerializableTrait;

	private readonly EffectFactory $factory;

	/**
	 * @var array(int=>array)
	 */
	private array $effects = [];

	/**
	 * @var Effect[]
	 */
	private array $aftercare = [];

	private bool $isLoaded = false;

	private ?array $iterator = null;

	private int $index = 0;

	private int $count = 0;

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
		$this->factory = new EffectFactory();
	}

	public function current(): ?Effect {
		return $this->iterator[$this->index] ?? null;
	}

	public function key(): string {
		return (string)$this->current();
	}

	public function next(): void {
		$this->index++;
	}

	public function rewind(): void {
		$this->iterator = [];
		foreach ($this->effects as $entities) {
			foreach ($entities as $effects) {
				foreach ($effects as $effect) {
					$this->iterator[] = $effect;
				}
			}
		}
		$this->index = 0;
		$this->count = count($this->iterator);
	}

	public function valid(): bool {
		if ($this->index < $this->count) {
			return true;
		}
		$this->iterator = null;
		return false;
	}

	/**
	 * Search for an existing Effect.
	 */
	public function find(Identifiable $effect): ?Effect {
		if ($effect instanceof Effect) {
			$namespace = $effect->Catalog()->value;
			$id        = $effect->Id()->Id();
			$class     = getClass($effect);
			return $this->effects[$namespace][$id][$class] ?? null;
		}
		throw new LemuriaException('Expected instance of Effect.');
	}

	/**
	 * @return Identifiable[]
	 */
	public function findAll(Identifiable $entity): array {
		$namespace = $entity->Catalog()->value;
		$id        = $entity->Id()->Id();
		return $this->effects[$namespace][$id] ?? [];
	}

	/**
	 * Add an Effect to persistence.
	 */
	public function add(Identifiable $effect): Score {
		$namespace = $effect->Catalog()->value;
		$id        = $effect->Id()->Id();
		$class     = getClass($effect);
		$this->effects[$namespace][$id][$class] = $effect;
		if ($this->isLoaded && $effect instanceof Effect && $effect->needsAftercare()) {
			$this->aftercare[] = $effect;
		}
		return $this;
	}

	/**
	 * Remove an Effect from persistence.
	 */
	public function remove(Identifiable $effect): Score {
		$namespace = $effect->Catalog()->value;
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

	/**
	 * @return Effect[]
	 */
	public function getAftercareEffects(): array {
		return $this->aftercare;
	}
}
