<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Message\Unit;

use Lemuria\Engine\Fantasya\Message\LemuriaMessage;
use Lemuria\Id;

class GriffineggReturnsMessage extends GriffineggAttackedMessage
{
	protected Id $region;

	protected function create(): string {
		return 'The griffin of unit ' . $this->id . ' returns to its aerie in region ' . $this->region . '.';
	}

	protected function getData(LemuriaMessage $message): void {
		parent::getData($message);
		$this->region = $message->get();
	}
}
