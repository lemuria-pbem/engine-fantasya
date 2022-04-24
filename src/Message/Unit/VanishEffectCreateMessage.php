<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Message\Unit;

use Lemuria\Engine\Fantasya\Message\LemuriaMessage;

class VanishEffectCreateMessage extends AbstractUnitMessage
{
	protected int $weeks;

	protected function create(): string {
		return 'Unit ' . $this->id . ' will vanish in ' . $this->weeks . ($this->weeks === 1 ? ' week.' : ' weeks.');
	}

	protected function getData(LemuriaMessage $message): void {
		parent::getData($message);
		$this->weeks = $message->getParameter();
	}

	protected function getTranslation(string $name): string {
		if ($name === 'weeks') {
			return $this->weeks . ' ' . $this->translateKey('replace.week', $this->weeks === 1 ? 0 : 1);
		}
		return parent::getTranslation($name);
	}
}
