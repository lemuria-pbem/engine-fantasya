<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Message\Region;

use Lemuria\Engine\Fantasya\Message\LemuriaMessage;
use Lemuria\Engine\Message\Result;
use Lemuria\Engine\Message\Section;

class AttackBattleMessage extends AbstractRegionMessage
{
	public const ATTACKER = 'attacker';

	public const DEFENDER = 'defender';

	protected Result $result = Result::EVENT;

	protected Section $section = Section::BATTLE;

	protected array $attacker;

	protected array $defender;

	protected function create(): string {
		return 'In region ' . $this->id . ' a battle takes place: ' . $this->attacker() . ' attack ' . $this->defender() . '.';
	}

	protected function getData(LemuriaMessage $message): void {
		parent::getData($message);
		$this->attacker = $message->getParameter(self::ATTACKER);
		$this->defender = $message->getParameter(self::DEFENDER);
	}

	protected function getTranslation(string $name): string {
		if ($name === self::ATTACKER) {
			return $this->concat($this->attacker, 'und');
		}
		if ($name === self::DEFENDER) {
			return $this->concat($this->defender, 'und');
		}
		return parent::getTranslation($name);
	}

	protected function attacker(): string {
		return $this->concat($this->attacker);
	}

	protected function defender(): string {
		return $this->concat($this->defender);
	}

	private function concat(array $parties, string $and = 'and'): string {
		$parties = array_values($parties);
		$last    = array_pop($parties);
		if (empty($parties)) {
			return $last;
		}
		return implode(', ', $parties) . ' ' . $and . ' ' . $last;
	}
}
