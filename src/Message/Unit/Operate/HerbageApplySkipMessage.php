<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Message\Unit\Operate;

use Lemuria\Engine\Fantasya\Message\LemuriaMessage;
use Lemuria\Engine\Message;

class HerbageApplySkipMessage extends HerbageApplyMessage
{
	protected string $level = Message::FAILURE;

	protected function create(): string {
		return 'The herb occurrence in almanac ' . $this->almanac . ' for region ' . $this->region . ' seems to be outdated.';
	}

	protected function getData(LemuriaMessage $message): void {
		parent::getData($message);
		$this->region = $message->get(self::REGION);
	}
}
