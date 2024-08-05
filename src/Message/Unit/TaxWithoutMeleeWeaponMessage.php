<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Message\Unit;

class TaxWithoutMeleeWeaponMessage extends TaxWithoutWeaponMessage
{
	protected string $weapon = 'melee weapon';
}
