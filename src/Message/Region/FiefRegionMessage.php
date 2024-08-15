<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Message\Region;

use Lemuria\Engine\Fantasya\Message\LemuriaMessage;
use Lemuria\Engine\Message\Result;
use Lemuria\Engine\Message\Section;

class FiefRegionMessage extends AbstractRegionMessage
{
	public final const string HEIR = 'heir';

	protected Result $result = Result::Event;

	protected Section $section = Section::Economy;

	protected string $realm;

	protected string $heir;

	protected function create(): string {
		return 'Party ' . $this->heir . ' now is the new ruler of realm ' . $this->realm . '.';
	}

	protected function getData(LemuriaMessage $message): void {
		parent::getData($message);
		$this->realm = $message->getParameter();
		$this->heir  = $message->getParameter(self::HEIR);
	}
}
