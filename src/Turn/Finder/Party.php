<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Turn\Finder;

use Lemuria\Model\Fantasya\Gathering;
use function Lemuria\getClass;
use Lemuria\Engine\Exception\EngineException;
use Lemuria\Id;
use Lemuria\Model\Fantasya\Monster;
use Lemuria\Model\Fantasya\Party as Model;
use Lemuria\Model\Fantasya\Party\Type;
use Lemuria\Model\Fantasya\Race;

class Party
{
	/**
	 * @var array<int, Id>
	 */
	protected array $byType = [];

	/**
	 * @var array<string, Id>
	 */
	protected array $byRace = [];

	public function Monster(): Gathering {
		$monster = new Gathering();
		foreach ($this->byType as $id) {
			$party = Model::get($id);
			if ($party->Type() === Type::Monster) {
				$monster->add($party);
			}
		}
		foreach ($this->byRace as $id) {
			$party = Model::get($id);
			if ($party->Type() === Type::Monster) {
				$monster->add($party);
			}
		}
		return $monster;
	}

	public function findByType(Type $type): Model {
		$id = $this->byType[$type->value] ?? null;
		if (!$id) {
			throw new EngineException('No option set for ' . __METHOD__ . '(' . $type->name . ').');
		}
		return Model::get($id);
	}

	public function findByRace(Race $race): Model {
		$class = getClass($race);
		$id    = $this->byType[$class] ?? null;
		if (!$id) {
			if ($race instanceof Monster) {
				return $this->findByType(Type::Monster);
			}
			throw new EngineException('No option set for ' . __METHOD__ . '(' . $class . ').');
		}
		return Model::get($id);
	}

	public function setId(Race|Type $key, Id $id): static {
		if ($key instanceof Type) {
			$this->byType[$key->value] = $id;
		} else {
			$this->byRace[getClass($key)] = $id;
		}
		return $this;
	}
}
