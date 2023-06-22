<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Message\Region;

use Lemuria\Engine\Fantasya\Message\LemuriaMessage;
use Lemuria\Engine\Message\Result;
use Lemuria\Engine\Message\Section;
use Lemuria\Id;

class RealmFoundedMessage extends AbstractRegionMessage
{
	protected Result $result = Result::Event;

	protected Section $section = Section::Economy;

	protected Id $party;

	protected string $realm;

	protected function create(): string {
		return 'In region ' . $this->id . ' the new realm ' . $this->realm . ' of party ' . $this->party . ' is founded.';
	}

	protected function getData(LemuriaMessage $message): void {
		parent::getData($message);
		$this->party = $message->get();
		$this->realm = $message->getParameter();
	}
}
