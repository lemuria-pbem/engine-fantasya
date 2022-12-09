<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Message\Unit;

use Lemuria\Engine\Fantasya\Message\LemuriaMessage;
use Lemuria\Engine\Message\Result;
use Lemuria\Id;

class UnicumCreateMessage extends UnicumNoMaterialMessage
{
	protected Result $result = Result::SUCCESS;

	protected Id $unicum;

	protected function create(): string {
		return 'Unit ' . $this->id . ' creates the new ' . $this->composition . ' ' . $this->unicum . '.';
	}

	protected function getData(LemuriaMessage $message): void {
		parent::getData($message);
		$this->unicum = $message->get();
	}
}
