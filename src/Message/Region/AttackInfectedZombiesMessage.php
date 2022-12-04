<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Message\Region;

use Lemuria\Engine\Fantasya\Message\LemuriaMessage;
use Lemuria\Engine\Message;
use Lemuria\Singleton;
use function Lemuria\getClass;

class AttackInfectedZombiesMessage extends AbstractRegionMessage
{
	protected string $level = Message::EVENT;

	protected int $size;

	protected Singleton $zombies;

	protected function create(): string {
		return 'In region ' . $this->id . ' a unit of ' . $this->size . ' ' . $this->zombies . ' rises from the slain in combat.';
	}

	protected function getData(LemuriaMessage $message): void {
		parent::getData($message);
		$this->size = $message->getParameter();
		$this->zombies = $message->getSingleton();
	}

	protected function getTranslation(string $name): string {
		if ($name === 'zombies') {
			return $this->translateKey('race.' . getClass($this->zombies), $this->size === 1 ? 0 : 1);
		}
		return parent::getTranslation($name);
	}
}