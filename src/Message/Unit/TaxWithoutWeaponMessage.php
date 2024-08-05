<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Message\Unit;

use Lemuria\Engine\Message\Result;
use Lemuria\Engine\Message\Section;

class TaxWithoutWeaponMessage extends AbstractUnitMessage
{
	protected Result $result = Result::Failure;

	protected Section $section = Section::Production;

	protected string $weapon = 'weapon';

	protected function create(): string {
		return 'Unit ' . $this->id . ' cannot use a ' . $this->weapon . ' to enforce tax payment.';
	}
}
