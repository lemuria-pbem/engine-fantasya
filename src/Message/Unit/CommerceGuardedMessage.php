<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Message\Unit;

use Lemuria\Engine\Fantasya\Message\LemuriaMessage;
use Lemuria\Engine\Fantasya\Message\Reliability;
use Lemuria\Id;

class CommerceGuardedMessage extends CommerceNotPossibleMessage
{
	public final const string PARTY = 'party';

	protected Reliability $reliability = Reliability::Unreliable;

	protected Id $party;

	protected function create(): string {
		return 'The guards of party ' . $this->party . ' have prohibited trading ' . $this->region . '.';
	}

	protected function getData(LemuriaMessage $message): void {
		parent::getData($message);
		$this->party = $message->get(self::PARTY);
	}
}
