<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Message\Unit;

use Lemuria\Engine\Fantasya\Message\LemuriaMessage;
use Lemuria\Engine\Message\Result;
use Lemuria\Engine\Message\Section;
use Lemuria\Id;

class TakeOfferPaymentMessage extends AbstractUnitMessage
{
	public final const string UNIT = 'unit';

	protected Result $result = Result::Failure;

	protected Section $section = Section::Economy;

	protected Id $unicum;

	protected Id $unit;

	protected function create(): string {
		return 'Unit ' . $this->unit . ' does not accept our payment for unicum ' . $this->unicum . '.';
	}

	protected function getData(LemuriaMessage $message): void {
		parent::getData($message);
		$this->unicum = $message->get();
		$this->unit   = $message->get(self::UNIT);
	}
}
