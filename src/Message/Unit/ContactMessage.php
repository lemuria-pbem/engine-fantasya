<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Message\Unit;

use Lemuria\Engine\Fantasya\Message\LemuriaMessage;
use Lemuria\Engine\Message\Section;
use Lemuria\Id;

class ContactMessage extends AbstractUnitMessage
{
	protected Section $section = Section::Mail;

	protected Id $unit;

	protected function create(): string {
		return 'Unit ' . $this->id . ' contacts unit ' . $this->unit . '.';
	}

	protected function getData(LemuriaMessage $message): void {
		parent::getData($message);
		$this->unit = $message->get();
	}
}
