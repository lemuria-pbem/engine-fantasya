<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Effect;

use Lemuria\Engine\Fantasya\Factory\CommandPriority;
use Lemuria\Engine\Fantasya\Message\Unit\Cast\AirshipCannotContinueMessage;
use Lemuria\Engine\Fantasya\Message\Unit\Cast\AirshipCannotLiftMessage;
use Lemuria\Engine\Fantasya\Message\Vessel\AirshipLiftMessage;
use Lemuria\Engine\Fantasya\Priority;
use Lemuria\Engine\Fantasya\State;
use Lemuria\Lemuria;
use Lemuria\Model\Fantasya\People;
use Lemuria\Model\Fantasya\Unicum;
use Lemuria\Model\Fantasya\Unit;

final class Airship extends AbstractVesselEffect
{
	private const WEIGHT = 100 * 100;

	private People $mages;

	private int $needed = 0;

	public function __construct(State $state) {
		parent::__construct($state, Priority::Middle);
		$this->mages = new People();
	}

	public function Mages(): People {
		return $this->mages;
	}

	public function needsAftercare(): bool {
		return true;
	}

	public function continueEffect(): bool {
		$this->calculateNeeded();
		$this->castSpell('keep the vessel floating');
		if ($this->needed > 0) {
			foreach ($this->Mages() as $unit /* @var Unit $unit */) {
				$this->message(AirshipCannotContinueMessage::class, $unit)->e($this->Vessel());
			}
			return false;
		}
		return true;
	}

	protected function run(): void {
		$isAftercare = $this->state->getCurrentPriority() > CommandPriority::AFTER_EFFECT;
		if ($isAftercare || !$this->liftUp()) {
			Lemuria::Score()->remove($this);
		}
	}

	private function calculateNeeded(): void {
		$vessel = $this->Vessel();
		$weight = $vessel->Ship()->Tare();
		foreach ($vessel->Passengers() as $unit /* @var Unit $unit */) {
			$weight += $unit->Weight();
		}
		foreach ($vessel->Treasury() as $unicum /* @var Unicum $unicum */) {
			$weight += $unicum->Composition()->Weight();
		}
		$this->needed = (int)ceil($weight / self::WEIGHT);
	}

	private function liftUp(): bool {
		$this->calculateNeeded();
		$this->castSpell('lift up the vessel');
		if ($this->needed > 0) {
			foreach ($this->Mages() as $unit /* @var Unit $unit */) {
				$this->message(AirshipCannotLiftMessage::class, $unit)->e($this->Vessel());
			}
			return false;
		}
		$this->message(AirshipLiftMessage::class, $this->Vessel());
		return true;
	}

	private function castSpell(string $debug): void {
		foreach ($this->mages as $unit /* @var Unit $unit */) {
			if ($this->needed <= 0) {
				break;
			}
			$aura      = $unit->Aura();
			$available = $aura->Aura();
			if ($available > 0) {
				$needed = min($aura, $this->needed);
				$aura->setAura($aura - $needed);
				$this->needed -= $needed;
				Lemuria::Log()->debug('Mage ' . $unit . ' consumes ' . $needed . ' aura to ' . $debug . '.');
			}
		}
	}
}
