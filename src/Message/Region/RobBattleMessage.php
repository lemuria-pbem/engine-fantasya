<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Message\Region;

use Lemuria\Engine\Fantasya\Message\LemuriaMessage;
use Lemuria\Engine\Message\Result;
use Lemuria\Engine\Message\Section;

class RobBattleMessage extends AbstractRegionMessage
{
	public final const string ROBBER = 'robber';

	public final const string VICTIM = 'victim';

	protected Result $result = Result::Event;

	protected Section $section = Section::Battle;

	protected string $robber;

	protected array $victim;

	protected function create(): string {
		return 'In region ' . $this->id . ' a robbery takes place: ' . $this->robber . ' assaults ' . $this->victim() . '.';
	}

	protected function getData(LemuriaMessage $message): void {
		parent::getData($message);
		$this->robber = $message->getParameter(self::ROBBER);
		$this->victim = $message->getParameter(self::VICTIM);
	}

	protected function getTranslation(string $name): string {
		if ($name === 'victim') {
			return $this->concat($this->victim, 'und');
		}
		return parent::getTranslation($name);
	}

	protected function victim(): string {
		return $this->concat($this->victim);
	}

	private function concat(array $units, string $and = 'and'): string {
		$units = array_values($units);
		$last  = array_pop($units);
		if (empty($units)) {
			return $last;
		}
		return implode(', ', $units) . ' ' . $and . ' ' . $last;
	}
}
