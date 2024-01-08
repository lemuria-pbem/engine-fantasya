<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Effect;

use Lemuria\Engine\Fantasya\Event\Act\Create;
use Lemuria\Engine\Fantasya\Event\Behaviour\Monster\Zombie;
use Lemuria\Engine\Fantasya\Factory\CommandPriority;
use Lemuria\Engine\Fantasya\Message\Unit\ControlEffectMessage;
use Lemuria\Engine\Fantasya\Message\Unit\ControlEffectNoneMessage;
use Lemuria\Engine\Fantasya\Message\Unit\ControlEffectOnlyMessage;
use Lemuria\Engine\Fantasya\Priority;
use Lemuria\Engine\Fantasya\State;
use Lemuria\Exception\UnserializeEntityException;
use Lemuria\Id;
use Lemuria\Identifiable;
use Lemuria\Lemuria;
use Lemuria\Model\Domain;
use Lemuria\Model\Fantasya\Gang;
use Lemuria\Model\Fantasya\Unit;
use Lemuria\Validate;

final class ControlEffect extends AbstractUnitEffect
{
	private const string AURA = 'aura';

	private const string SUMMONER = 'summoner';

	private float $aura = 0.0;

	private int $summoner;

	public function __construct(State $state) {
		parent::__construct($state, Priority::Before);
	}

	public function Aura(): float {
		return $this->aura;
	}

	public function Summoner(): Unit {
		return Unit::get(new Id($this->summoner));
	}

	public function needsAftercare(): bool {
		return true;
	}

	public function reassign(Id $oldId, Identifiable $identifiable): void {
		parent::reassign($oldId, $identifiable);
		if ($identifiable->Catalog() === Domain::Unit && $this->summoner === $oldId->Id()) {
			$this->summoner = $identifiable->Id()->Id();
		}
	}

	public function remove(Identifiable $identifiable): void {
		parent::remove($identifiable);
		if ($identifiable->Catalog() === Domain::Unit && $this->summoner === $identifiable->Id()->Id()) {
			$this->deliver();
		}
	}

	public function serialize(): array {
		$data                 = parent::serialize();
		$data[self::AURA]     = $this->aura;
		$data[self::SUMMONER] = $this->summoner;
		return $data;
	}

	public function unserialize(array $data): static {
		parent::unserialize($data);
		$this->aura     = $data[self::AURA];
		$this->summoner = $data[self::SUMMONER];
		return $this;
	}

	public function setAura(float $aura): ControlEffect {
		$this->aura = $aura;
		return $this;
	}

	public function setSummoner(Unit $summoner): ControlEffect {
		$this->summoner = $summoner->Id()->Id();
		return $this;
	}

	public function deliver(): void {
		Lemuria::Score()->remove($this);
		$this->addZombieBehaviour($this->Unit());
		Lemuria::Log()->debug($this->Unit() . ' is delivered from its summoner.');
	}

	/**
	 * @throws UnserializeEntityException
	 */
	protected function validateSerializedData(array $data): void {
		parent::validateSerializedData($data);
		$this->validate($data, self::AURA, Validate::Float);
		$this->validate($data, self::SUMMONER, Validate::Int);
	}

	protected function run(): void {
		if ($this->state->getCurrentPriority() > CommandPriority::AFTER_EFFECT) {
			$this->checkSummoner();
		} else {
			$this->maintainControl();
		}
	}

	private function checkSummoner(): void {
		$summoner = $this->Summoner();
		if ($summoner->Size() <= 0) {
			Lemuria::Score()->remove($this);
			Lemuria::Log()->debug($this->Unit() . ' is delivered as its summoner ' . $summoner . ' does not exist anymore.');
		} else {
			$from = $this->Unit()->Region();
			$to   = $summoner->Region();
			if ($to !== $from && Lemuria::World()->getDistance($from, $to) > 1) {
				Lemuria::Score()->remove($this);
				Lemuria::Log()->debug($this->Unit() . ' is delivered as its summoner ' . $summoner . ' has run away.');
			}
		}
	}

	private function maintainControl(): void {
		$unit = $this->Unit();
		$size = $unit->Size();
		if ($size > 0 && $this->aura > 0.0) {
			$control  = (int)ceil($size * $this->aura);
			$summoner = $this->Summoner();
			$aura     = $this->Summoner()->Aura();
			$reserve  = (int)$aura?->Aura();
			if ($reserve >= $control) {
				$aura->consume($control);
				$this->message(ControlEffectMessage::class, $summoner)->e($unit)->p($control);
			} else {
				$aura->setAura(0);
				$count = (int)floor($reserve / $this->aura);
				if ($count > 0) {
					$unit->setSize($count);
					$controlled = new Gang($unit->Race(), $count);
					$this->message(ControlEffectOnlyMessage::class, $summoner)->e($unit)->p($reserve)->i($controlled);

					$uncontrolled = new Gang($unit->Race(), $size - $count);
					$create       = new Create($unit->Party(), $unit->Region());
					Lemuria::Log()->debug($uncontrolled . ' of ' . $unit . ' split apart as new unit in ' . $unit->Region() . '.');
					foreach ($create->add($uncontrolled)->act()->getUnits() as $zombies) {
						$this->addZombieBehaviour($zombies);
					}
				} else {
					Lemuria::Score()->remove($this);
					$this->addZombieBehaviour($unit);
					$this->message(ControlEffectNoneMessage::class, $summoner)->e($unit);
				}
			}
		}
	}

	private function addZombieBehaviour(Unit $unit): void {
		$behaviour = new Zombie($unit);
		$this->state->addMonster($behaviour->prepare());
		Lemuria::Log()->debug('Behaviour for uncontrolled zombies has been added.');
	}
}
