<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Message\Unit;

use Lemuria\Engine\Fantasya\Message\LemuriaMessage;
use Lemuria\Engine\Message;

class HelpMessage extends AbstractUnitMessage
{
	protected string $level = Message::SUCCESS;

	protected int $agreement;

	protected function create(): string {
		return 'Unit ' . $this->id . ' has set relation ' . $this->agreement . ' to all parties.';
	}

	protected function getData(LemuriaMessage $message): void {
		parent::getData($message);
		$this->agreement = $message->getParameter();
	}

	protected function getTranslation(string $name): string {
		if ($name === 'agreement') {
			$agreement = $this->translateKey('diplomacy.relation.agreement_' . $this->agreement);
			if ($agreement) {
				return $agreement;
			}
		}
		return parent::getTranslation($name);
	}
}
