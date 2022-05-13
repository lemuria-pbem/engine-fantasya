<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Message\Unit\Cast;

use Lemuria\Engine\Fantasya\Message\LemuriaMessage;
use Lemuria\Engine\Message;
use Lemuria\Id;

class AstralPassageRegionMessage extends AbstractCastMessage
{
	protected string $level = Message::SUCCESS;

	protected Id $target;

	protected function create(): string {
		return 'Unit ' . $this->id . ' teleports to region ' . $this->target . '.';
	}

	protected function getData(LemuriaMessage $message): void {
		parent::getData($message);
		$this->target = $message->get();
	}
}
