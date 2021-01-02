<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Lemuria\Message\Party;

use Lemuria\Engine\Lemuria\Message\LemuriaMessage;
use Lemuria\Engine\Message;
use Lemuria\Id;
use Lemuria\Model\Lemuria\Region;

class OriginMessage extends AbstractPartyMessage
{
	public const REGION = 'region';

	protected string $level = Message::SUCCESS;

	protected Id $region;

	protected string $name;

	protected function create(): string {
		return 'Map origin has been set to region ' . $this->name . ' [' . $this->region . '].';
	}

	protected function getData(LemuriaMessage $message): void {
		parent::getData($message);
		$this->region = $message->get(self::REGION);
		$this->name   = Region::get($this->region)->Name();
	}
}