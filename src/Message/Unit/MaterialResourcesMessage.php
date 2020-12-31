<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Lemuria\Message\Unit;

use Lemuria\Engine\Lemuria\Message\LemuriaMessage;
use Lemuria\Engine\Message;
use Lemuria\Singleton;

class MaterialResourcesMessage extends AbstractUnitMessage
{
	protected string $level = Message::FAILURE;

	protected Singleton $material;

	protected function create(): string {
		return 'Unit ' . $this->id . ' has no resources to produce ' . $this->material . '.';
	}

	protected function getData(LemuriaMessage $message): void {
		parent::getData($message);
		$this->material = $message->getSingleton();
	}
}
