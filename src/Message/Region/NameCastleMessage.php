<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Message\Region;

use Lemuria\Engine\Fantasya\Message\LemuriaMessage;
use Lemuria\Engine\Message\Result;
use Lemuria\Id;

class NameCastleMessage extends AbstractRegionMessage
{
	protected Result $result = Result::FAILURE;

	protected Id $owner;

	protected function create(): string {
		return 'Unit ' . $this->owner . ' is not owner of the biggest castle in region '. $this->id . ' and thus cannot rename it.';
	}

	protected function getData(LemuriaMessage $message): void {
		parent::getData($message);
		$this->owner = $message->get();
	}
}
