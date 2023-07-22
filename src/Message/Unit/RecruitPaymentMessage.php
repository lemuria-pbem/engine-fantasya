<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Message\Unit;

use Lemuria\Engine\Fantasya\Message\Casus;
use Lemuria\Engine\Fantasya\Message\LemuriaMessage;
use Lemuria\Engine\Message\Result;
use Lemuria\Model\Fantasya\Quantity;

class RecruitPaymentMessage extends RecruitMessage
{
	protected Result $result = Result::Debug;

	protected Quantity $cost;

	protected function create(): string {
		return 'Unit ' . $this->id . ' pays ' . $this->cost . ' for ' . $this->size . ' recruits.';
	}

	protected function getData(LemuriaMessage $message): void {
		parent::getData($message);
		$this->cost = $message->getQuantity();
	}

	protected function getTranslation(string $name): string {
		return $this->item($name, 'cost', casus: Casus::Adjective) ?? parent::getTranslation($name);
	}
}
