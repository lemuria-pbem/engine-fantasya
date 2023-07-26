<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Factory\Model;

use Lemuria\Exception\ParseEnumException;

enum Disease
{
	case GoblinPlague;

	public static function parse(string $name): Disease {
		foreach (self::cases() as $case) {
			if ($name === $case->name) {
				return $case;
			}
		}
		throw new ParseEnumException(__CLASS__, $name);
	}
}
