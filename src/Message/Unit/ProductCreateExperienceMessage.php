<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Lemuria\Message\Unit;

use Lemuria\Engine\Lemuria\Message\LemuriaMessage;
use Lemuria\Engine\Message;
use Lemuria\Singleton;

class ProductCreateExperienceMessage extends AbstractUnitMessage
{
	public const TALENT = 'talent';

	public const ARTIFACT = 'artifact';

	protected string $level = Message::FAILURE;

	protected Singleton $talent;

	protected Singleton $artifact;

	/**
	 * @return string
	 */
	protected function create(): string {
		return 'Unit ' . $this->Id() . ' has not enough experience in ' . $this->talent . ' to create ' . $this->artifact . '.';
	}

	/**
	 * @param LemuriaMessage $message
	 */
	protected function getData(LemuriaMessage $message): void {
		parent::getData($message);
		$this->talent = $message->getSingleton(self::TALENT);
		$this->artifact = $message->getSingleton(self::ARTIFACT);
	}
}
