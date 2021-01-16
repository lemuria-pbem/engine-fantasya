<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Lemuria\Message\Region;

use Lemuria\Engine\Lemuria\Message\LemuriaMessage;
use Lemuria\Engine\Message;
use Lemuria\Id;

class DescribeCastleMessage extends AbstractRegionMessage
{
	protected string $level = Message::FAILURE;

	protected Id $owner;

	protected function create(): string {
		return 'Unit ' . $this->owner . ' is not owner of the biggest castle in region '. $this->id . ' and thus cannot describe it.';
	}

	protected function getData(LemuriaMessage $message): void {
		parent::getData($message);
		$this->owner = $message->get();
	}
}
