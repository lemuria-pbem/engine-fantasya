<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Factory;

final class Workplaces
{
	public const CAMEL = 1;

	public const ELEPHANT = 5;

	public const HORSE = 1;

	public const TREE = 10;

	public function getUsed(int $horse = 0, int $camel = 0, int $elephant = 0, int $tree = 0): int {
		return $horse * self::HORSE + $camel * self::CAMEL + $elephant * self::ELEPHANT + $tree * self::TREE;
	}
}
