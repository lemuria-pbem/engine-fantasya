<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Lemuria\Message\Unit;

use Lemuria\Engine\Lemuria\Message\LemuriaMessage;
use Lemuria\Engine\Message;
use Lemuria\Singleton;

class ArtifactExperienceMessage extends AbstractUnitMessage
{
	public const TALENT = 't';

	public const ARTIFACT = 'a';

	protected string $level = Message::FAILURE;

	protected Singleton $talent;

	protected Singleton $artifact;

	protected function create(): string {
		return 'Unit ' . $this->id . ' has not enough experience in ' . $this->talent . ' to create ' . $this->artifact . '.';
	}

	protected function getData(LemuriaMessage $message): void {
		parent::getData($message);
		$this->talent = $message->getSingleton(self::TALENT);
		$this->artifact = $message->getSingleton(self::ARTIFACT);
	}
}
