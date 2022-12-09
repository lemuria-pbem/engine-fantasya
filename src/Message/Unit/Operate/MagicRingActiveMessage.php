<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Message\Unit\Operate;

use Lemuria\Engine\Fantasya\Message\LemuriaMessage;
use Lemuria\Engine\Fantasya\Message\Unit\AbstractUnitMessage;
use Lemuria\Engine\Message\Result;
use Lemuria\Engine\Message\Section;
use Lemuria\Id;
use Lemuria\Singleton;

class MagicRingActiveMessage extends AbstractUnitMessage
{
	protected Result $result = Result::SUCCESS;

	protected Section $section = Section::MAGIC;

	protected Id $unicum;

	protected Singleton $composition;

	protected function create(): string {
		return 'The effect of ' . $this->composition . ' ' . $this->unicum . ' is still active.';
	}

	protected function getData(LemuriaMessage $message): void {
		parent::getData($message);
		$this->unicum      = $message->get();
		$this->composition = $message->getSingleton();
	}

	protected function getTranslation(string $name): string {
		return $this->composition($name, 'composition') ?? parent::getTranslation($name);
	}
}
