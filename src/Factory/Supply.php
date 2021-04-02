<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Factory;

use Lemuria\Exception\LemuriaException;
use Lemuria\Model\Fantasya\Commodity\Peasant;
use Lemuria\Model\Fantasya\Luxury;
use Lemuria\Model\Fantasya\Offer;
use Lemuria\Model\Fantasya\Region;

class Supply implements \Countable
{
	private ?Luxury $luxury = null;

	private ?Offer $offer = null;

	private int $count = 0;

	private int $peasants;

	private float $step;

	private int $max = 0;

	private bool $isOffer = false;

	public function __construct(private Region $region) {
		$resources      = $region->Resources();
		$this->peasants = $resources[Peasant::class]->Count();
		$this->step     = $this->peasants / 100.0;
	}

	public function count(): int {
		return $this->max;
	}

	/**
	 * Check if more of the current luxury is available.
	 */
	public function hasMore(): bool {
		return $this->count < $this->max;
	}

	/**
	 * Reserve one piece of the current luxury and get its price.
	 */
	public function one(): int {
		if (!$this->hasMore()) {
			throw new LemuriaException('Cannot reserve more of the luxury.');
		}
		$factor = (int)floor($this->count++ / $this->step);
		if ($this->isOffer) {
			return ($factor + 1) * $this->luxury->Value();
		}
		return $this->offer->Price() - $factor * $this->luxury->Value();
	}

	/**
	 * Set the luxury and reset the calculator.
	 */
	public function setLuxury(Luxury $luxury): Supply {
		$luxuries = $this->region->Luxuries();
		if ($luxuries) {
			$this->luxury = $luxury;
			if ($luxuries->Offer()->Commodity() === $luxury) {
				$this->offer   = $luxuries->Offer();
				$this->max     = $this->peasants;
				$this->isOffer = true;
			} else {
				$this->offer   = $luxuries[$luxury];
				$price         = $this->offer->Price();
				$this->max     = (int)floor(ceil($price / $luxury->Value()) * $this->step);
				$this->isOffer = false;
			}

		} else {
			$this->max = 0;
		}
		$this->count = 0;
		return $this;
	}
}
