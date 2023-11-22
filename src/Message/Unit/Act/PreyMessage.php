<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Message\Unit\Act;

use Lemuria\Engine\Fantasya\Message\LemuriaMessage;
use Lemuria\Id;

class PreyMessage extends SeekMessage
{
	public final const string PREY = 'prey';

	protected Id $prey;

	protected function create(): string {
		return 'Unit ' . $this->id . ' has selected unit ' . $this->prey . ' as prey in region ' . $this->region . '.';
	}

	protected function getData(LemuriaMessage $message): void {
		parent::getData($message);
		$this->prey = $message->get(self::PREY);
	}
}
