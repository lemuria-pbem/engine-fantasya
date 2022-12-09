<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Message\Unit\Cast;

use Lemuria\Engine\Fantasya\Message\LemuriaMessage;
use Lemuria\Engine\Message\Result;
use Lemuria\Id;

class AstralPassageRegionMessage extends AbstractCastMessage
{
	protected Result $result = Result::Success;

	protected Id $target;

	protected function create(): string {
		return 'Unit ' . $this->id . ' teleports to region ' . $this->target . '.';
	}

	protected function getData(LemuriaMessage $message): void {
		parent::getData($message);
		$this->target = $message->get();
	}
}
