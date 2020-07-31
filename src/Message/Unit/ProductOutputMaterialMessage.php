<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Lemuria\Message\Unit;

use Lemuria\Engine\Lemuria\Message\LemuriaMessage;
use Lemuria\Engine\Message;
use Lemuria\Singleton;

class ProductOutputMaterialMessage extends AbstractUnitMessage
{
	protected string $level = Message::FAILURE;

	private Singleton $material;

	/**
	 * @return string
	 */
	protected function create(): string {
		return 'Unit ' . $this->Id() . ' has no resources to produce ' . $this->material . '.';
	}

	/**
	 * @param LemuriaMessage $message
	 */
	protected function getData(LemuriaMessage $message): void {
		parent::getData($message);
		$this->material = $message->getSingleton();
	}
}
