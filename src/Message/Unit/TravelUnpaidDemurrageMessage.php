<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Message\Unit;

use Lemuria\Engine\Fantasya\Message\LemuriaMessage;

class TravelUnpaidDemurrageMessage extends TravelSimulationMessage
{
	protected string $direction;

	protected function create(): string {
		return 'Unit ' . $this->id . ' cannot leave the port ' . $this->direction . ' because there is unpaid demurrage.';
	}

	protected function getData(LemuriaMessage $message): void {
		parent::getData($message);
		$this->direction = $message->getParameter();
	}

	protected function getTranslation(string $name): string {
		return $this->direction($name) ?? parent::getTranslation($name);
	}
}
