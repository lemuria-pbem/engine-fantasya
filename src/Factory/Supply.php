<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Factory;

use JetBrains\PhpStorm\Pure;

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

	/**
	 * Get the current Luxury.
	 *
	 * @throws LemuriaException
	 */
	public function Luxury(): Luxury {
		if (!$this->luxury) {
			throw new LemuriaException('The Luxury has not been set.');
		}
		return $this->luxury;
	}

	/**
	 * Get the current price of the Luxury.
	 *
	 * @throws LemuriaException
	 */
	public function Price(): int {
		if (!$this->luxury) {
			throw new LemuriaException('The Luxury has not been set.');
		}
		$factor = (int)floor($this->count / $this->step);
		if ($this->isOffer) {
			return ($factor + 1) * $this->luxury->Value();
		}
		return $this->offer->Price() - $factor * $this->luxury->Value();
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
	 * Estimate total cost of given number of items.
	 */
	#[Pure] public function estimate(int $count): int {
		$count = min($count, $this->count());
		$steps = (int)floor($count / $this->step);
		$rest  = $count % (int)floor($this->step);
		$value = $this->luxury->Value();
		$total = 0.0;
		$i     = 0;

		if ($this->isOffer) {
			$price = $value;
			while ($i++ < $steps) {
				$total += $this->step * $price;
				$price += $value;
			}
		} else {
			$price = $this->offer->Price();
			while ($i++ < $steps) {
				$total += $this->step * $price;
				$price -= $value;
			}
		}

		$total += $rest * $price;
		return (int)ceil($total);
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
