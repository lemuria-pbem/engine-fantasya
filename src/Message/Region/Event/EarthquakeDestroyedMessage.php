<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Message\Region\Event;

use Lemuria\Engine\Fantasya\Message\LemuriaMessage;
use Lemuria\Engine\Fantasya\Message\Region\AbstractRegionMessage;
use Lemuria\Engine\Message;
use Lemuria\Id;

class EarthquakeDestroyedMessage extends AbstractRegionMessage
{
	protected string $level = Message::FAILURE;

	protected Id $construction;

	protected function create(): string {
		return 'Construction ' . $this->construction . ' has been destroyed completely by the earthquake.';
	}

	protected function getData(LemuriaMessage $message): void {
		parent::getData($message);
		$this->construction = $message->get();
	}
}
