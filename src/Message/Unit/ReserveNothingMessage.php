<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Message\Unit;

use Lemuria\Engine\Fantasya\Message\LemuriaMessage;
use Lemuria\Engine\Message\Result;
use Lemuria\Engine\Message\Section;
use Lemuria\Singleton;

class ReserveNothingMessage extends AbstractUnitMessage
{
	protected Result $result = Result::FAILURE;

	protected Section $section = Section::PRODUCTION;

	protected Singleton $commodity;

	protected function create(): string {
		return 'Unit ' . $this->id . ' cannot reserve any ' . $this->commodity . ' from the pool.';
	}

	protected function getData(LemuriaMessage $message): void {
		parent::getData($message);
		$this->commodity = $message->getSingleton();
	}

	protected function getTranslation(string $name): string {
		return $this->commodity($name, 'commodity') ?? parent::getTranslation($name);
	}
}
