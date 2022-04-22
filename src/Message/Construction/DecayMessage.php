<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Message\Construction;

use Lemuria\Engine\Message;
use Lemuria\Singleton;

class DecayMessage extends AbstractConstructionMessage
{
	protected string $level = Message::EVENT;

	protected Singleton $building;

	protected function create(): string {
		return 'The ravages of time let the ' . $this->building . ' ' . $this->id . ' decay.';
	}
}
