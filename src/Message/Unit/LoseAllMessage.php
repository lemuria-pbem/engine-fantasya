<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Message\Unit;

use Lemuria\Engine\Fantasya\Message\LemuriaMessage;
use Lemuria\Engine\Message\Result;
use Lemuria\Singleton;

class LoseAllMessage extends AbstractUnitMessage
{
	protected Result $result = Result::Success;

	protected Singleton $commodity;

	protected function create(): string {
		return 'Unit ' . $this->id . ' throws away all ' . $this->commodity . '.';
	}

	protected function getData(LemuriaMessage $message): void {
		parent::getData($message);
		$this->commodity = $message->getSingleton();
	}

	protected function getTranslation(string $name): string {
		return $this->singleton($name, 'commodity') ?? parent::getTranslation($name);
	}
}
