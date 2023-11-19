<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Travel;

use function Lemuria\getClass;
use Lemuria\Exception\LemuriaException;
use Lemuria\Model\Fantasya\Animal;
use Lemuria\Model\Fantasya\Commodity\Camel;
use Lemuria\Model\Fantasya\Commodity\Elephant;
use Lemuria\Model\Fantasya\Commodity\Griffin;
use Lemuria\Model\Fantasya\Commodity\Horse;
use Lemuria\Model\Fantasya\Commodity\Pegasus;
use Lemuria\Model\Fantasya\Talent\Riding;

enum Transport
{
	case LAND;

	case WATER;

	case NO_CAPACITY;

	case TOO_HEAVY;

	case NO_RIDING;

	public static function check(Trip $trip): self {
		$weight   = $trip->Weight();
		$capacity = $trip->Capacity();
		if ($weight > $capacity) {
			return self::TOO_HEAVY;
		}
		if ($weight === $capacity) {
			return self::NO_CAPACITY;
		}
		if ($trip->Movement() === Movement::Ship) {
			return self::WATER;
		}
		$calculus = $trip->Calculus();
		$unit     = $calculus->Unit();
		$riding   = $unit->Size() * $calculus->knowledge(Riding::class)->Level();
		if ($riding < $trip->Knowledge()) {
			return Transport::NO_RIDING;
		}
		return self::LAND;
	}

	public static function requiredRidingLevel(Animal $animal): int {
		return match ($animal::class) {
			Horse::class, Camel::class, Pegasus::class => 1,
			Elephant::class                            => 2,
			Griffin::class                             => 6,
			default => throw new LemuriaException('A unit cannot ride a ' . getClass($animal) . '.')
		};
	}
}
