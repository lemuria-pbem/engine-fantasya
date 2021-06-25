<?php
declare(strict_types = );
namespace Lemuria\Engine\Fantasya\Message\Unit;

use Lemuria\Engine\Message;

class CastMessage extends CastExperienceMessage
{
	protected string $level = Message::SUCCESS;

	protected function create(): string {
		return 'Unit ' . $this->id . ' casts ' . $this->spell . '.';
	}
}
