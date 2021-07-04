<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Message\Unit;

use Lemuria\Engine\Fantasya\Message\LemuriaMessage;
use Lemuria\Engine\Message;

class RecreateAuraMessage extends AbstractUnitMessage
{
	protected string $level = Message::EVENT;

	protected int $points;

	protected function create(): string {
		return 'Unit ' . $this->id . ' regains ' . $this->points . ' aura points.';
	}

	protected function getData(LemuriaMessage $message): void {
		parent::getData($message);
		$this->points = $message->getParameter();
	}
}
