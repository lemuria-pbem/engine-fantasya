<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Message\Unit;

use Lemuria\Engine\Fantasya\Message\LemuriaMessage;
use Lemuria\Engine\Message;
use Lemuria\Singleton;

class PotionEffectEndsMessage extends AbstractUnitMessage
{
	protected string $level = Message::EVENT;

	protected Singleton $potion;

	protected function create(): string {
		return 'The effect of ' . $this->potion . ' ends.';
	}

	protected function getData(LemuriaMessage $message): void {
		parent::getData($message);
		$this->potion = $message->getSingleton();
	}

	protected function getTranslation(string $name): string {
		return $this->commodity($name, 'potion') ?? parent::getTranslation($name);
	}
}
