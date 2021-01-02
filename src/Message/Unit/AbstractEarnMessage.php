<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Lemuria\Message\Unit;

use Lemuria\Engine\Lemuria\Message\LemuriaMessage;
use Lemuria\Engine\Message;
use Lemuria\Item;
use Lemuria\Singleton;

abstract class AbstractEarnMessage extends AbstractUnitMessage
{
	protected string $level = Message::SUCCESS;

	protected Item $income;

	protected Singleton $talent;

	abstract public function __construct();

	protected function create(): string {
		return 'Unit ' . $this->id . ' earns ' . $this->income . ' with ' . $this->talent . '.';
	}

	protected function getData(LemuriaMessage $message): void {
		parent::getData($message);
		$this->income = $message->getQuantity();
	}

	protected function getTranslation(string $name): string {
		$income = $this->item($name, 'income');
		if ($income) {
			return $income;
		}
		$talent = $this->talent($name, 'talent');
		if ($talent) {
			return $talent;
		}
		return parent::getTranslation($name);
	}
}
