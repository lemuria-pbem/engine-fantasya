<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Command;

use Lemuria\Engine\Fantasya\Exception\UnknownCommandException;

/**
 * Use a potion.
 *
 * - BENUTZEN <potion>
 * - BENUTZEN <amount> <potion>
 */
final class Apply extends UnitCommand
{
	protected function initialize(): void {
		parent::initialize();
		throw new UnknownCommandException($this);
	}

	protected function run(): void {
		//TODO: Check for existing ApplyEffect.
	}
}
