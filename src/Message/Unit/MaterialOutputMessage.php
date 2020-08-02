<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Lemuria\Message\Unit;

use Lemuria\Engine\Lemuria\Message\LemuriaMessage;
use Lemuria\Engine\Message;
use Lemuria\Item;
use Lemuria\Singleton;

class MaterialOutputMessage extends AbstractUnitMessage
{
	protected string $level = Message::SUCCESS;

	protected Item $output;

	protected Singleton $talent;

	/**
	 * @return string
	 */
	protected function create(): string {
		return 'Unit ' . $this->id . ' produces ' . $this->output . ' with ' . $this->talent . '.';
	}

	/**
	 * @param LemuriaMessage $message
	 */
	protected function getData(LemuriaMessage $message): void {
		parent::getData($message);
		$this->output = $message->getQuantity();
		$this->talent = $message->getSingleton();
	}
}
