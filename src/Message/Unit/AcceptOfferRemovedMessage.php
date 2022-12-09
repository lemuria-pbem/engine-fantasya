<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Message\Unit;

use Lemuria\Engine\Fantasya\Message\LemuriaMessage;
use Lemuria\Engine\Message\Result;
use Lemuria\Engine\Message\Section;
use Lemuria\Id;

class AcceptOfferRemovedMessage extends AbstractUnitMessage
{
	protected Result $result = Result::Debug;

	protected Section $section = Section::Economy;

	protected Id $trade;

	protected function create(): string {
		return 'Offer ' . $this->trade . ' has been terminated.';
	}

	protected function getData(LemuriaMessage $message): void {
		parent::getData($message);
		$this->trade = $message->get();
	}
}
