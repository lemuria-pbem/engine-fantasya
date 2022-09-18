<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Message\Unit;

use Lemuria\Engine\Fantasya\Message\LemuriaMessage;
use Lemuria\Engine\Message;
use Lemuria\Engine\Message\Section;
use Lemuria\Id;

class AcceptOfferRemovedMessage extends AbstractUnitMessage
{
	protected string $level = Message::DEBUG;

	protected Section $section = Section::ECONOMY;

	protected Id $trade;

	protected function create(): string {
		return 'Offer ' . $this->trade . ' has been terminated.';
	}

	protected function getData(LemuriaMessage $message): void {
		parent::getData($message);
		$this->trade = $message->get();
	}
}
