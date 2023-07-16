<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Realm;

use Lemuria\Engine\Fantasya\Context;
use Lemuria\Engine\Fantasya\State;
use Lemuria\Exception\LemuriaException;
use Lemuria\Model\Fantasya\Intelligence;
use Lemuria\Model\Fantasya\Party;
use Lemuria\Model\Fantasya\Quantity;
use Lemuria\Model\Fantasya\Realm;
use Lemuria\Model\Fantasya\Region;
use Lemuria\Model\Fantasya\Unit;

class Fund
{
	protected ?Fleet $fleet = null;

	private Party $party;

	private Region $central;

	private Intelligence $intelligence;

	private ?Unit $unit = null;

	public function __construct(protected readonly Realm $realm, protected readonly Context $context) {
		$this->party        = $realm->Party();
		$this->central      = $realm->Territory()->Central();
		$this->intelligence = new Intelligence($this->central);
	}

	public function take(Quantity $quantity): Quantity {
		$commodity = $quantity->Commodity();
		$weight    = $quantity->Weight();
		$fleet     = $this->fleet();
		$capacity  = $fleet->Outgoing();
		if ($capacity < $weight) {
			$amount   = (int)floor($capacity / $commodity->Weight());
			$quantity = new Quantity($commodity, $amount);
			if ($amount <= 0) {
				return $quantity;
			}
		}

		$unit = $this->unit();
		if (!$unit) {
			return new Quantity($commodity, 0);
		}
		$quantity = $this->context->getResourcePool($unit)->take($unit, $quantity);
		$weight   = $quantity->Weight();
		if ($weight > 0) {
			if ($this->fleet()->send($weight) < $weight) {
				throw new LemuriaException('Could not send all weight.');
			}
		}
		return $quantity;
	}

	protected function fleet(): Fleet {
		if (!$this->fleet) {
			$this->fleet = State::getInstance()->getRealmFleet($this->realm);
		}
		return $this->fleet;
	}

	protected function unit(): ?Unit {
		if (!$this->unit || $this->unit->Party() !== $this->party || $this->unit->Region() !== $this->central) {
			$this->unit = $this->intelligence->getUnits($this->party)->getFirst();
		}
		return $this->unit;
	}
}
