<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Lemuria\Message\Unit;

use Lemuria\Engine\Lemuria\Message\LemuriaMessage;
use Lemuria\Engine\Message;
use Lemuria\Singleton;

class RawMaterialResourcesMessage extends AbstractUnitMessage
{
	protected string $level = Message::FAILURE;

	protected Singleton $material;

	/**
	 * @return string
	 */
	protected function create(): string {
		return 'Unit ' . $this->Id() . ' cannot find any ' . $this->material . '.';
	}

	/**
	 * @param LemuriaMessage $message
	 */
	protected function getData(LemuriaMessage $message): void {
		parent::getData($message);
		$this->material = $message->getSingleton();
	}
}
