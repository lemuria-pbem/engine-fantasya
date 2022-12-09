<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Message\Party;

use Lemuria\Engine\Fantasya\Message\LemuriaMessage;
use Lemuria\Engine\Message\Result;
use Lemuria\Id;

class OriginUntoldMessage extends AbstractPartyMessage
{
	protected Result $result = Result::Failure;

	protected Id $party;

	protected function create(): string {
		return 'Map origin cannot be set to party ' . $this->party . ', we do not know where it is.';
	}

	protected function getData(LemuriaMessage $message): void {
		parent::getData($message);
		$this->party = $message->get();
	}
}
