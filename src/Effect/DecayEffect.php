<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Effect;

use function Lemuria\getClass;
use Lemuria\Engine\Fantasya\Message\Construction\DecayMessage;
use Lemuria\Engine\Fantasya\Message\Construction\DecayToRuinMessage;
use Lemuria\Engine\Fantasya\Priority;
use Lemuria\Engine\Fantasya\State;
use Lemuria\Exception\UnserializeEntityException;
use Lemuria\Lemuria;
use Lemuria\Model\Dictionary;
use Lemuria\Model\Fantasya\Building\Ruin;
use Lemuria\Model\Fantasya\Factory\BuilderTrait;
use Lemuria\Validate;

/**
 * This effect slowly reduces a construction's size until it is converted to a ruin.
 */
final class DecayEffect extends AbstractConstructionEffect
{
	use BuilderTrait;

	public final const int MONUMENT = 4 * 2 * 3;

	private const string AGE = 'age';

	private const string INTERVAL = 'interval';

	private int $age = 0;

	private int $interval = 1;

	public function __construct(State $state) {
		parent::__construct($state, Priority::After);
	}

	public function Age(): int {
		return $this->age;
	}

	public function Interval(): int {
		return $this->interval;
	}

	public function serialize(): array {
		$data                 = parent::serialize();
		$data[self::AGE]      = $this->age;
		$data[self::INTERVAL] = $this->interval;
		return $data;
	}

	public function unserialize(array $data): static {
		parent::unserialize($data);
		$this->age      = $data[self::AGE];
		$this->interval = $data[self::INTERVAL];
		return $this;
	}

	public function resetAge(): DecayEffect {
		$this->age = 0;
		return $this;
	}

	public function setInterval(int $interval): DecayEffect {
		$this->interval = max(1, $interval);
		return $this;
	}

	/**
	 * @throws UnserializeEntityException
	 */
	protected function validateSerializedData(array $data): void {
		parent::validateSerializedData($data);
		$this->validate($data, self::AGE, Validate::Int);
		$this->validate($data, self::INTERVAL, Validate::Int);
	}

	protected function run(): void {
		if ($this->age >= $this->interval) {
			$this->age = 0;
			if ($this->letConstructionDecay()) {
				Lemuria::Score()->remove($this);
				return;
			}
		}
		$this->age++;
	}

	private function letConstructionDecay(): bool {
		$construction = $this->Construction();
		$building     = $construction->Building();
		$size         = $construction->Size();
		if ($size <= 1) {
			$construction->setSize(0);
			$construction->setBuilding(self::createBuilding(Ruin::class));
			$description = $construction->Description();
			if ($description) {
				$dictionary = new Dictionary();
				$key        = 'description.decay.' . getClass($building);
				if ($dictionary->has($key)) {
					$construction->setDescription($description . ' ' . $dictionary->get($key));
				}
			}
			$construction->Inhabitants()->clear();
			$this->message(DecayToRuinMessage::class, $construction)->s($building);
			Lemuria::Log()->debug($building . ' ' . $construction . ' has decayed to ruins.');
			return true;
		}
		$construction->setSize(--$size);
		$this->message(DecayMessage::class, $construction)->s($building);
		Lemuria::Log()->debug($building . ' ' . $construction . ' decays once more.');
		return false;
	}
}
