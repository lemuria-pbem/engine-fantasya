<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Message\Unit;

use Lemuria\Engine\Fantasya\Message\LemuriaMessage;
use Lemuria\Id;

class CommerceNotPossibleMessage extends CommerceSiegeMessage
{
	protected Id $region;

	protected function create(): string {
		return 'Unit ' . $this->id . ' cannot trade in region ' . $this->region . ' - no castle with market here.';
	}

	protected function getData(LemuriaMessage $message): void {
		parent::getData($message);
		$this->region = $message->get();
	}
}
