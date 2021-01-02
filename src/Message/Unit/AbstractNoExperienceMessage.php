<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Lemuria\Message\Unit;

use Lemuria\Engine\Message;
use Lemuria\Model\Lemuria\Factory\BuilderTrait;
use Lemuria\Model\Lemuria\Talent;

abstract class AbstractNoExperienceMessage extends AbstractUnitMessage
{
	use BuilderTrait;

	protected string $level = Message::FAILURE;

	protected function create(): string {
		return 'Unit ' . $this->id . ' has no experience in ' . $this->getTalent() . '.';
	}

	abstract protected function getTalent(): Talent;
}
