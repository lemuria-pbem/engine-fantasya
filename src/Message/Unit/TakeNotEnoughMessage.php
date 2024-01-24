<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Message\Unit;

use Lemuria\Engine\Message\Result;
use Lemuria\Engine\Fantasya\Message\LemuriaMessage;
use Lemuria\Engine\Message\Section;
use Lemuria\Id;
use Lemuria\Singleton;

class TakeNotEnoughMessage extends AbstractUnitMessage
{
	protected Result $result = Result::Failure;

	protected Section $section = Section::Economy;

	protected Id $unicum;

	protected Singleton $payment;

	protected function create(): string {
		return 'Unit ' . $this->id . ' has not enough ' . $this->payment . ' to pay the unicum ' . $this->unicum . '.';
	}

	protected function getData(LemuriaMessage $message): void {
		parent::getData($message);
		$this->unicum  = $message->get();
		$this->payment = $message->getSingleton();
	}

	protected function getTranslation(string $name): string {
		return $this->singleton($name, 'payment', 1)?? parent::getTranslation($name);
	}
}
