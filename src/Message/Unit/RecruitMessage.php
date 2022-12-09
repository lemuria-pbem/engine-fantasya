<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Message\Unit;

use Lemuria\Engine\Fantasya\Message\LemuriaMessage;
use Lemuria\Engine\Message\Result;
use Lemuria\Engine\Message\Section;

class RecruitMessage extends AbstractUnitMessage
{
	protected Result $result = Result::SUCCESS;

	protected Section $section = Section::PRODUCTION;

	protected int $size;

	protected function create(): string {
		return 'Unit ' . $this->id . ' recruits ' . $this->size . ' peasants.';
	}

	protected function getData(LemuriaMessage $message): void {
		parent::getData($message);
		$this->size = $message->getParameter();
	}

	protected function getTranslation(string $name): string {
		return $this->number($name, 'size') ?? parent::getTranslation($name);
	}
}
