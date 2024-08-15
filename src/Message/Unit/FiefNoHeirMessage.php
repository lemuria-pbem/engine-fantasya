<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Message\Unit;

use Lemuria\Engine\Fantasya\Message\LemuriaMessage;
use Lemuria\Id;

class FiefNoHeirMessage extends FiefNoneMessage
{
	protected Id $region;

	protected string $heir;

	protected function create(): string {
		return 'Party ' . $this->heir . ' has no unit in region ' . $this->region . ' to hand over the realm.';
	}

	protected function getData(LemuriaMessage $message): void {
		parent::getData($message);
		$this->region = $message->get();
		$this->heir   = $message->getParameter();
	}
}
