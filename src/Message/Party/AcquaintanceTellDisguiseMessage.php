<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Message\Party;

use Lemuria\Engine\Fantasya\Message\LemuriaMessage;

class AcquaintanceTellDisguiseMessage extends AcquaintanceTellMessage
{
	public final const string DISGUISED = 'disguised';

	protected string $disguised;

	protected function create(): string {
		return 'We have initiated diplomatic relations to ' . $this->party . ' and told unit ' . $this->unit . ' false information about the people of ' . $this->disguised . '.';
	}

	protected function getData(LemuriaMessage $message): void {
		parent::getData($message);
		$this->disguised = $message->getParameter(self::DISGUISED);
	}
}
