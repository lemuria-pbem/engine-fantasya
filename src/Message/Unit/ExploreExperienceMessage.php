<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Message\Unit;

use Lemuria\Engine\Message;
use Lemuria\Engine\Message\Section;

class ExploreExperienceMessage extends AbstractUnitMessage
{
	protected string $level = Message::FAILURE;

	protected int $section = Section::PRODUCTION;

	protected function create(): string {
		return 'Unit ' . $this->id . ' has not enough experience in herbal lore to explore the region.';
	}
}
