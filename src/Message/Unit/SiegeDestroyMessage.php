<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Message\Unit;

use Lemuria\Engine\Fantasya\Message\LemuriaMessage;
use Lemuria\Engine\Fantasya\Message\Region\AbstractRegionMessage;
use Lemuria\Engine\Message\Result;
use Lemuria\Engine\Message\Section;
use Lemuria\Id;

class SiegeDestroyMessage extends AbstractRegionMessage
{
	protected Result $result = Result::Event;

	protected Section $section = Section::Battle;

	protected Id $construction;

	protected function create(): string {
		return 'The construction ' . $this->construction . ' has been destroyed.';
	}

	protected function getData(LemuriaMessage $message): void {
		parent::getData($message);
		$this->construction = $message->get();
	}
}
