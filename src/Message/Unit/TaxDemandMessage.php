<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Lemuria\Message\Unit;

use Lemuria\Engine\Lemuria\Message\LemuriaMessage;
use Lemuria\Engine\Message;

class TaxDemandMessage extends AbstractUnitMessage
{
	public const COLLECTORS = 'collectors';

	public const RATE = 'rate';

	protected string $level = Message::DEBUG;

	protected int $collectors;

	protected int $rate;

	protected function create(): string {
		return 'Unit ' . $this->id . ' has ' . $this->collectors . ' tax collectors with demand of ' . $this->rate . ' silver.';
	}

	protected function getData(LemuriaMessage $message): void {
		parent::getData($message);
		$this->collectors = $message->getParameter(self::COLLECTORS);
		$this->rate = $message->getParameter(self::RATE);
	}
}
