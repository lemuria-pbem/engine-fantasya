<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Message\Party\Administrator;

use Lemuria\Engine\Fantasya\Message\LemuriaMessage;
use Lemuria\Engine\Fantasya\Message\Party\AbstractPartyMessage;
use Lemuria\Engine\Message;

class OvercrowdedMessage extends AbstractPartyMessage
{
	public const REGION = 'region';

	protected string $level = Message::EVENT;

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
