<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Message\Unit\Act;

use Lemuria\Engine\Fantasya\Message\LemuriaMessage;
use Lemuria\Id;

class SeekMessage extends RoamMessage
{
	protected Id $region;

	protected function create(): string {
		return 'Unit ' . $this->id . ' has found enemies in region ' . $this->region . '.';
	}

	protected function getData(LemuriaMessage $message): void {
		parent::getData($message);
		$this->region = $message->get();
	}
}
