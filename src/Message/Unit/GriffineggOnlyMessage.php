<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Message\Unit;

use Lemuria\Engine\Fantasya\Message\LemuriaMessage;
use Lemuria\Item;

class GriffineggOnlyMessage extends GriffineggNoneMessage
{
	protected Item $eggs;

	protected function create(): string {
		return 'Unit ' . $this->id . ' could find only ' . $this->eggs . ' griffin eggs.';
	}

	protected function getData(LemuriaMessage $message): void {
		parent::getData($message);
		$this->eggs = $message->getQuantity();
	}
}
