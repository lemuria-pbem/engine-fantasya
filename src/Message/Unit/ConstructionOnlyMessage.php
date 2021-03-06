<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Message\Unit;

use Lemuria\Engine\Fantasya\Message\LemuriaMessage;

class ConstructionOnlyMessage extends ConstructionResourcesMessage
{
	protected int $size;

	protected function create(): string {
		return 'Unit ' . $this->id . ' can only build ' . $this->size . ' points in size on construction ' . $this->construction . '.';
	}

	protected function getData(LemuriaMessage $message): void {
		parent::getData($message);
		$this->size = $message->getParameter();
	}
}
