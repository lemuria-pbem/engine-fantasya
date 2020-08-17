<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Lemuria\Message\Unit;

use Lemuria\Engine\Lemuria\Message\LemuriaMessage;
use Lemuria\Engine\Message;
use Lemuria\Singleton;

class ArtifactResourcesMessage extends AbstractUnitMessage
{
	protected string $level = Message::FAILURE;

	protected Singleton $artifact;

	/**
	 * @return string
	 */
	protected function create(): string {
		return 'Unit ' . $this->id . ' has no material to create ' . $this->artifact . '.';
	}

	/**
	 * @param LemuriaMessage $message
	 */
	protected function getData(LemuriaMessage $message): void {
		parent::getData($message);
		$this->artifact = $message->getSingleton();
	}
}
