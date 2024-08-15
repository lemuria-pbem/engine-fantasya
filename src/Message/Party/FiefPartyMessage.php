<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Message\Party;

use Lemuria\Engine\Fantasya\Message\LemuriaMessage;
use Lemuria\Engine\Message\Result;
use Lemuria\Engine\Message\Section;

class FiefPartyMessage extends AbstractPartyMessage
{
	public final const string HEIR = 'heir';

	protected Result $result = Result::Event;

	protected Section $section = Section::Economy;

	protected string $realm;

	protected function create(): string {
		return 'We are now the new ruler of the realm ' . $this->realm . '.';
	}

	protected function getData(LemuriaMessage $message): void {
		parent::getData($message);
		$this->realm = $message->getParameter();
	}
}
