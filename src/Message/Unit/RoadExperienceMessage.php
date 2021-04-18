<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Message\Unit;

use Lemuria\Engine\Fantasya\Message\LemuriaMessage;
use Lemuria\Singleton;

class RoadExperienceMessage extends RoadInOceanMessage
{
	protected Singleton $talent;

	protected function create(): string {
		return 'Unit ' . $this->id . ' has not enough experience in ' . $this->talent . ' to build a road in region ' . $this->region . '.';
	}

	protected function getData(LemuriaMessage $message): void {
		parent::getData($message);
		$this->talent = $message->getSingleton();
	}
}
