<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Message\Construction;

use Lemuria\Engine\Message\Section;

class UpkeepAbandonedMessage extends AbstractConstructionMessage
{
	protected Section $section = Section::ECONOMY;

	protected function create(): string {
		return 'Construction ' . $this->id . ' is abandoned, nobody has paid its upkeep.';
	}
}
