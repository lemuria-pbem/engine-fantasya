<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Message\Unit;

use Lemuria\Engine\Message\Section;

class HerbNoDemandMessage extends AbstractUnitMessage
{
	protected Section $section = Section::Production;

	protected function create(): string {
		return 'Unit ' . $this->id . ' cannot produce herbs, no demand.';
	}
}
