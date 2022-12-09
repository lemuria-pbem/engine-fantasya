<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Message\Unit;

use Lemuria\Engine\Fantasya\Message\LemuriaMessage;
use Lemuria\Engine\Message\Result;
use Lemuria\Engine\Message\Section;
use Lemuria\Id;

class RoadInOceanMessage extends AbstractUnitMessage
{
	protected Result $result = Result::FAILURE;

	protected Section $section = Section::PRODUCTION;

	protected Id $region;

	protected function create(): string {
		return 'Unit ' . $this->id . ' cannot build a road in ' . $this->region . '.';
	}

	protected function getData(LemuriaMessage $message): void {
		parent::getData($message);
		$this->region = $message->get();
	}
}
