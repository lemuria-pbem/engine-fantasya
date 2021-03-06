<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Message\Unit;

use Lemuria\Engine\Fantasya\Message\LemuriaMessage;
use Lemuria\Singleton;

class ConstructionExperienceMessage extends ConstructionResourcesMessage
{
	protected Singleton $talent;

	protected function create(): string {
		return 'Unit ' . $this->id . ' is not skilled enough in ' . $this->talent . ' to build on construction ' . $this->construction . '.';
	}

	protected function getData(LemuriaMessage $message): void {
		parent::getData($message);
		$this->talent = $message->getSingleton();
	}
}
