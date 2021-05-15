<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Message\Unit;

use Lemuria\Engine\Fantasya\Message\LemuriaMessage;

class WoundshutDamageMessage extends AbstractUnitMessage
{
	protected int $damage;

	protected function create(): string {
		return 'Unit ' . $this->id . ' has damage of ' . $this->damage . ' hitpoints.';
	}

	protected function getData(LemuriaMessage $message): void {
		parent::getData($message);
		$this->damage = $message->getParameter();
	}

	protected function getTranslation(string $name): string {
		return $this->number($name, 'damage') ?? parent::getTranslation($name);
	}
}
