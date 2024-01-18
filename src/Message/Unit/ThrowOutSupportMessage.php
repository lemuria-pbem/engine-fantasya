<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Message\Unit;

use Lemuria\Engine\Fantasya\Message\LemuriaMessage;
use Lemuria\Engine\Message\Result;
use Lemuria\Id;

class ThrowOutSupportMessage extends ThrowOutOwnMessage
{
	public final const string SUPPORT = 'support';

	protected Result $result = Result::Event;

	protected Id $support;

	protected function create(): string {
		return 'Units of party ' . $this->support . ' have supported unit ' . $this->unit . ' against us.';
	}

	public function getData(LemuriaMessage $message): void {
		parent::getData($message);
		$this->support = $message->get(self::SUPPORT);
	}
}
