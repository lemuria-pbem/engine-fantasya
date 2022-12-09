<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Message\Unit;

use Lemuria\Engine\Fantasya\Message\LemuriaMessage;
use Lemuria\Engine\Message\Result;
use Lemuria\Engine\Message\Section;
use Lemuria\Id;

class ConstructionResourcesMessage extends AbstractUnitMessage
{
	protected Result $result = Result::FAILURE;

	protected Section $section = Section::PRODUCTION;

	protected Id $construction;

	protected function create(): string {
		return 'Unit ' . $this->id . ' has no material to build on construction ' . $this->construction . '.';
	}

	protected function getData(LemuriaMessage $message): void {
		parent::getData($message);
		$this->construction = $message->get();
	}
}
