<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Message\Party\Administrator;

use Lemuria\Engine\Fantasya\Message\LemuriaMessage;
use Lemuria\Engine\Fantasya\Message\Party\AbstractPartyMessage;
use Lemuria\Engine\Message\Result;

class OvercrowdedMessage extends AbstractPartyMessage
{
	public final const REGION = 'region';

	protected Result $result = Result::Event;

	protected string $construction;

	protected string $region;

	protected function create(): string {
		return 'Your construction ' . $this->construction . ' in region ' . $this->region . ' is overcrowded. Please take care that excess people leave it.';
	}

	protected function getData(LemuriaMessage $message): void {
		parent::getData($message);
		$this->construction = $message->getParameter();
		$this->region       = $message->getParameter(self::REGION);
	}
}
