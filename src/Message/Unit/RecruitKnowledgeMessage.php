<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Message\Unit;

use Lemuria\Engine\Fantasya\Message\LemuriaMessage;
use Lemuria\Engine\Message\Section;

class RecruitKnowledgeMessage extends AbstractUnitMessage
{
	protected Section $section = Section::PRODUCTION;

	protected int $percent;

	protected function create(): string {
		return 'Knowledge of unit ' . $this->id . ' has decreased to ' . $this->percent . ' percent.';
	}

	protected function getData(LemuriaMessage $message): void {
		parent::getData($message);
		$this->percent = $message->getParameter();
	}

	protected function getTranslation(string $name): string {
		return $this->number($name, 'percent') ?? parent::getTranslation($name);
	}
}
