<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Message\Unit\Operate;

use Lemuria\Engine\Fantasya\Message\LemuriaMessage;
use Lemuria\Engine\Fantasya\Message\Unit\AbstractUnitMessage;
use Lemuria\Engine\Message\Section;
use Lemuria\Id;
use Lemuria\Singleton;

abstract class AbstractOperateMessage extends AbstractUnitMessage
{
	protected Section $section = Section::Magic;

	protected Id $unicum;

	protected Singleton $composition;

	protected function getData(LemuriaMessage $message): void {
		parent::getData($message);
		$this->unicum      = $message->get();
		$this->composition = $message->getSingleton();
	}

	protected function getTranslation(string $name): string {
		return $this->composition($name, 'composition') ?? parent::getTranslation($name);
	}
}
