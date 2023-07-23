<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Message\Unit;

use Lemuria\Engine\Fantasya\Message\Casus;
use Lemuria\Engine\Fantasya\Message\LemuriaMessage;
use Lemuria\Engine\Message\Result;
use Lemuria\Model\Fantasya\Quantity;

class QuotaSetMessage extends QuotaRemoveHerbMessage
{
	protected Result $result = Result::Success;

	protected Quantity $quota;

	public function create(): string {
		return 'Unit ' . $this->id . ' sets a quota of ' . $this->quota . ' in region ' . $this->region . '.';
	}

	protected function getData(LemuriaMessage $message): void {
		parent::getData($message);
		$this->quota = $message->getQuantity();
	}

	protected function getTranslation(string $name): string {
		return $this->item($name, 'quota', casus: Casus::Adjective) ?? parent::getTranslation($name);
	}
}
