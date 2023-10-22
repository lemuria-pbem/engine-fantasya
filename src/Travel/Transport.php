<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Travel;

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
}
