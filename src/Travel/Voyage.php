<?php
declare (strict_types = 1);
namespace Lemuria\Engine\Fantasya\Travel;

use Lemuria\Engine\Fantasya\Calculus;
use Lemuria\Item;
use Lemuria\Model\Fantasya\Commodity\Camel;
use Lemuria\Model\Fantasya\Commodity\Carriage;
use Lemuria\Model\Fantasya\Commodity\Elephant;
use Lemuria\Model\Fantasya\Commodity\Griffin;
use Lemuria\Model\Fantasya\Commodity\Horse;
use Lemuria\Model\Fantasya\Commodity\Pegasus;
use Lemuria\Model\Fantasya\Commodity\Potion\GoliathWater;
use Lemuria\Model\Fantasya\Commodity\Potion\SevenLeagueTea;
use Lemuria\Model\Fantasya\Commodity\Weapon\Catapult;
use Lemuria\Model\Fantasya\Commodity\Weapon\WarElephant;
use Lemuria\Model\Fantasya\Factory\BuilderTrait;
use Lemuria\Model\Fantasya\Quantity;
use Lemuria\Model\Fantasya\Race\Troll;
use Lemuria\Model\Fantasya\Talent\Riding;
use Lemuria\Model\Fantasya\Transport;

/**
 * Helper for travel calculations.
 */
final class Voyage
{
	use BuilderTrait;

	public function __construct(private readonly ?Calculus $calculus = null) {
	}

	/**
	 * Calculate the trip for travelling.
	 *
	 * @return Trip
	 */
	public function trip(): Trip {
		$unit   = $this->calculus->Unit();
		$vessel = $unit->Vessel();
		if ($vessel) {
			$ship    = $vessel->Ship();
			$weight  = $vessel->Passengers()->Weight();
			$payload = (int)floor($vessel->Completion() * $ship->Payload());
			return new Trip(0, $payload, Movement::Ship, $weight, $ship->Speed(), $ship->Crew());
		}

		$race         = $unit->Race();
		$size         = $unit->Size();
		$racePayload  = $race->Payload();
		$boostSize    = $this->calculus->hasApplied(GoliathWater::class)?->Count() * GoliathWater::PERSONS;
		$payloadBoost = min($size, $boostSize);
		$payload      = $size * $racePayload + $payloadBoost * ($this->payload(Horse::class) - $racePayload);
		$inventory    = $unit->Inventory();
		$horse        = $inventory[Horse::class] ?? null;
		$camel        = $inventory[Camel::class] ?? null;
		$elephant     = $inventory[Elephant::class] ?? null;
		$warElephant  = $inventory[WarElephant::class] ?? null;
		$griffin      = $inventory[Griffin::class] ?? null;
		$pegasus      = $inventory[Pegasus::class] ?? null;

		$carriage = $inventory[Carriage::class] ?? null;
		$catapult = $inventory[Catapult::class] ?? null;
		$caCount  = $carriage?->Count() + $catapult?->Count();
		$weight   = $this->weight($unit->Weight(), [$carriage, $catapult, $horse, $camel, $elephant, $warElephant, $griffin, $pegasus]);

		$ride       = $this->transport($carriage) + $this->transport($catapult);
		$ride      += $this->transport($camel) + $this->transport($elephant) + $this->transport($warElephant);
		$ride      += $this->transport($horse, $caCount * 2);
		$fly        = $this->transport($griffin) + $this->transport($pegasus);
		$rideFly    = $ride + $fly;
		$walk       = $payload + $rideFly;
		$riding     = $size * $this->calculus->knowledge(Riding::class)->Level();
		$boostSize  = $this->calculus->hasApplied(SevenLeagueTea::class)?->Count() * SevenLeagueTea::PERSONS;
		$speedBoost = ($boostSize >= $size ? 2 : 1) * $race->Speed();

		if ($caCount > 0) {
			$cars        = (int)$carriage?->Count();
			$speed       = $this->speed([$carriage, $catapult, $horse, $camel, $elephant, $warElephant, $griffin, $pegasus]);
			$animals     = [$horse, $camel, $elephant, $warElephant, $griffin, $pegasus];
			$talentDrive = $this->talent($animals, $size, true, $cars);
			$horseCount  = $horse?->Count();
			if ($riding >= $talentDrive && $horseCount >= 2 * $caCount && $weight <= $rideFly && !$catapult) {
				return new Trip($walk, $rideFly, Movement::Drive, $weight, $speed, $talentDrive, $speedBoost);
			}
			$talentWalk = $this->talent($animals, $size, false, $cars);
			if ($horseCount >= 2 * $caCount && $riding >= $talentWalk) {
				$weight -= $size * $race->Weight();
				return new Trip($walk, $rideFly, Movement::Walk, $weight, 1, $talentWalk, $speedBoost);
			}
			if ($race instanceof Troll) {
				$needed = 2 * $caCount;
				if ($size >= $needed) {
					$riding     = $needed * $this->calculus->knowledge(Riding::class)->Level();
					$talentWalk = $this->talent($animals, $size - $needed) + $riding;
					$weight    -= $size * $race->Weight();
					return new Trip($walk, $rideFly, Movement::Walk, $weight, $speedBoost, $talentWalk);
				}
			}
			$walk   = $this->transport($camel) + $this->transport($elephant) + $this->transport($warElephant)+ $this->transport($horse);
			$walk  += $this->transport($griffin) + $this->transport($pegasus) + $payload;
			$weight = $unit->Weight() - $size * $race->Weight();
			return new Trip($walk, $rideFly, Movement::Walk, $weight, $speedBoost, $talentDrive);
		}
		if ($fly > 0 && !$horse && !$camel && !$elephant && !$warElephant) {
			$animals    = [$griffin, $pegasus];
			$speed      = $this->speed($animals);
			$talentFly  = $this->talent($animals, $size, true);
			$talentWalk = $this->talent($animals, $size);
			if ($riding >= $talentFly) {
				return new Trip($walk, $fly, Movement::Fly, $weight, $speed, [$talentFly, $talentWalk], $speedBoost);
			}
		}
		if ($rideFly > 0 && $weight <= $rideFly) {
			$speed      = $this->speed([$horse, $camel, $elephant, $warElephant]);
			$animals    = [$horse, $camel, $elephant, $warElephant, $griffin, $pegasus];
			$talentRide = $this->talent($animals, $size, true);
			$talentWalk = $this->talent($animals, $size);
			if ($riding >= $talentRide) {
				return new Trip($walk, $rideFly, Movement::Ride, $weight, $speed, [$talentRide, $talentWalk], $speedBoost);
			}
		}
		$weight -= $size * $race->Weight();
		$speed   = $this->speed([$horse, $camel, $elephant, $warElephant], $race->Speed());
		$animals = [$horse, $camel, $elephant, $warElephant, $griffin, $pegasus];
		$talent  = $this->talent($animals, $size);
		return new Trip($walk, $rideFly, Movement::Walk, $weight, $speed, $talent, $speedBoost);
	}

	private function transport(?Item $quantity, int $reduceBy = 0): int {
		$transport = $quantity?->getObject();
		if ($transport instanceof Transport) {
			return max($quantity->Count() - $reduceBy, 0) * $transport->Payload();
		}
		return 0;
	}

	/**
	 * @param array<Quantity|null> $goods
	 */
	private function weight(int $total, array $goods): int {
		foreach ($goods as $quantity) {
			if ($quantity) {
				$total -= $quantity->Weight();
			}
		}
		return $total;
	}

	/**
	 * @param array<Item|null> $transports
	 */
	private function speed(array $transports, int $speed = PHP_INT_MAX): int {
		foreach ($transports as $item) {
			$transport = $item?->getObject();
			if ($transport instanceof Transport) {
				$speed = min($speed, $transport->Speed());
			}
		}
		return $speed < PHP_INT_MAX ? $speed : $this->calculus->Unit()->Race()->Speed();
	}

	/**
	 * @param array<Item|null> $transports
	 * @noinspection PhpMissingBreakStatementInspection
	 */
	private function talent(array $transports, int $size, bool $max = false, int $carriage = 0): int {
		$talent = 0;
		foreach ($transports as $item) {
			if ($item) {
				$transport = $item->getObject();
				$count     = $item->Count();
				switch ($transport::class) {
					case Horse::class :
						$count = max($count, $carriage * 2);
					case Camel::class :
						if ($max) {
							$talent += $count;
						} elseif ($count > $size) {
							$talent += $count - $size;
						}
						break;
					case Elephant::class :
					case WarElephant::class :
						$talent += $count * 2;
						break;
					case Pegasus::class :
						$talent += $count * 3;
						break;
					case Griffin::class :
						$talent += $count * 6;
				}
			}
		}
		if ($carriage) {
			$talent = max($talent, $max ? $carriage * 2 : $carriage);
		}
		return $talent;
	}

	private function payload(string $class): int {
		/** @var Transport $transport */
		$transport = self::createCommodity($class);
		return $transport->Payload();
	}
}
