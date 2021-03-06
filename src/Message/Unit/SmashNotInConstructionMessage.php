<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Message\Unit;

use Lemuria\Engine\Fantasya\Message\LemuriaMessage;

class SmashNotInConstructionMessage extends AbstractUnitMessage
{
	protected string $level = LemuriaMessage::FAILURE;

	protected function create(): string {
		return 'Unit ' . $this->id . ' must be inside the construction to destroy it.';
	}
}
