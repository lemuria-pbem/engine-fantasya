<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Message\Vessel;

use Lemuria\Engine\Fantasya\Message\LemuriaMessage;
use Lemuria\Engine\Message;

class DriftMessage extends AbstractVesselMessage
{
	protected string $level = Message::EVENT;

	protected string $direction;

	protected function create(): string {
		return 'Vessel ' . $this->id . ' drifts ' . $this->direction . ' because captain and crew cannot steer it anymore.';
	}

	protected function getData(LemuriaMessage $message): void {
		parent::getData($message);
		$this->direction = $message->getParameter();
	}
}
