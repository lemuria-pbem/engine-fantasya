<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Message\Unit;

use Lemuria\Engine\Fantasya\Message\LemuriaMessage;
use Lemuria\Singleton;

class CastExperienceMessage extends CastNoMagicianMessage
{
	protected Singleton $spell;

	protected function create(): string {
		return 'Unit ' . $this->id . ' has not enough experience in Magic to cast ' . $this->spell . '.';
	}

	protected function getData(LemuriaMessage $message): void {
		parent::getData($message);
		$this->spell = $message->getSingleton();
	}

	protected function getTranslation(string $name): string {
		return $this->spell($name, 'spell') ?? parent::getTranslation($name);
	}
}
