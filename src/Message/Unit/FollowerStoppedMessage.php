<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Message\Unit;

use Lemuria\Engine\Fantasya\Message\LemuriaMessage;
use Lemuria\Engine\Fantasya\Message\Reliability;
use Lemuria\Id;

class FollowerStoppedMessage extends FollowerMessage
{
	public final const string REGION = 'region';

	protected Reliability $reliability = Reliability::Unreliable;

	protected Id $region;

	protected function create(): string {
		return 'Our follower ' . $this->follower . ' was stopped in region ' . $this->region . ' by the guards.';
	}

	protected function getData(LemuriaMessage $message): void {
		parent::getData($message);
		$this->region = $message->get(self::REGION);
	}
}
