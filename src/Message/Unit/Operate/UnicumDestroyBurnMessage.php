<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Message\Unit\Operate;

use Lemuria\Engine\Fantasya\Message\LemuriaMessage;
use Lemuria\Engine\Fantasya\Message\Unit\AbstractUnitMessage;
use Lemuria\Engine\Message\Result;
use Lemuria\Engine\Message\Section;
use Lemuria\Id;
use Lemuria\Singleton;

class UnicumDestroyBurnMessage extends AbstractUnitMessage
{
	protected Result $result = Result::Success;

	protected Section $section = Section::Economy;

	protected Singleton $composition;

	protected Id $unicum;

	protected function create(): string {
		return 'Unit ' . $this->id . ' lights up the ' . $this->composition . ' ' . $this->unicum . ' and burns it into ashes.';
	}

	protected function getData(LemuriaMessage $message): void {
		parent::getData($message);
		$this->composition = $message->getSingleton();
		$this->unicum      = $message->get();
	}

	protected function getTranslation(string $name): string {
		return $this->singleton($name, 'composition') ?? parent::getTranslation($name);
	}
}
