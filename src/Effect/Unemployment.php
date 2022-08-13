<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Effect;

use Lemuria\Engine\Fantasya\Message\Region\UnemploymentMessage;
use Lemuria\Engine\Fantasya\Priority;
use Lemuria\Engine\Fantasya\State;
use Lemuria\Exception\UnserializeEntityException;
use Lemuria\Id;
use Lemuria\Lemuria;
use Lemuria\Model\Fantasya\Continent;
use Lemuria\Model\Fantasya\Region;
use Lemuria\Serializable;

final class Unemployment extends AbstractContinentEffect
{
	/**
	 * @var array<int, Unemployment>
	 */
	private static array $unemployment = [];

	/**
	 * @var array<int, int>
	 */
	private array $peasants = [];

	public static function getFor(Region $region): Unemployment {
		$continent = $region->Continent();
		$id        = $continent->Id()->Id();
		if (!isset(self::$unemployment[$id])) {
			self::$unemployment[$id] = self::findUnemployment($continent);
		}
		return self::$unemployment[$id];
	}

	public function __construct(State $state) {
		parent::__construct($state, Priority::BEFORE);
	}

	public function getPeasants(Region $region): ?int {
		return $this->peasants[$region->Id()->Id()] ?? null;
	}

	public function setPeasants(Region $region, int $peasants): Unemployment {
		$this->peasants[$region->Id()->Id()] = $peasants;
		return $this;
	}

	public function serialize(): array {
		$data = parent::serialize();
		$data['peasants'] = $this->peasants;
		return $data;
	}

	public function unserialize(array $data): Serializable {
		parent::unserialize($data);
		$this->peasants = $data['peasants'];
		return $this;
	}

	/**
	 * @param array (string=>mixed) &$data
	 * @throws UnserializeEntityException
	 */
	protected function validateSerializedData(array &$data): void {
		parent::validateSerializedData($data);
		$this->validate($data, 'peasants', 'array');
	}

	protected function run(): void {
		foreach ($this->peasants as $id => $peasants) {
			$region = Region::get(new Id($id));
			if ($region->Landscape()->Workplaces() > 0) {
				$this->message(UnemploymentMessage::class, $region)->p($peasants);
			}
		}
	}

	private static function findUnemployment(Continent $continent): Unemployment {
		$unemployment = new Unemployment(State::getInstance());
		$existing     = Lemuria::Score()->find($unemployment->setContinent($continent));
		if ($existing instanceof Unemployment) {
			return $existing;
		}
		Lemuria::Score()->add($unemployment);
		return $unemployment;
	}
}
