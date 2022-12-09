<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Message\Party;

use Lemuria\Engine\Fantasya\Message\LemuriaMessage;
use Lemuria\Engine\Message\Result;
use Lemuria\Id;
use Lemuria\Singleton;

class ObtainmentMessage extends AbstractPartyMessage
{
	protected Result $result = Result::Success;

	protected Id $unit;

	protected Singleton $spell;

	protected function create(): string {
		return 'Unit ' . $this->unit . ' has learned a new spell for our spell book: ' . $this->spell;
	}

	protected function getData(LemuriaMessage $message): void {
		parent::getData($message);
		$this->unit  = $message->get();
		$this->spell = $message->getSingleton();
	}

	protected function getTranslation(string $name): string {
		return $this->spell($name, 'spell') ?? parent::getTranslation($name);
	}
}
