<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Message\Unit;

use Lemuria\Engine\Fantasya\Message\LemuriaMessage;
use Lemuria\Engine\Message\Section;

class LearnEffectivityMessage extends AbstractUnitMessage
{
	protected Section $section = Section::STUDY;

	protected float $effectivity;

	protected function create(): string {
		return 'Unit ' . $this->id . ' aboard has learning effectivity ' . $this->effectivity . '.';
	}

	protected function getData(LemuriaMessage $message): void {
		parent::getData($message);
		$this->effectivity = $message->getParameter();
	}

	protected function getTranslation(string $name): string {
		return $this->number($name, 'effectivity') ?? parent::getTranslation($name);
	}
}
