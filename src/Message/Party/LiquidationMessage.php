<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Lemuria\Message\Party;

use Lemuria\Engine\Lemuria\Message\LemuriaMessage;
use Lemuria\Engine\Message;
use Lemuria\Id;

class LiquidationMessage extends AbstractPartyMessage
{
	protected string $level = Message::SUCCESS;

	protected Id $unit;

    protected function create(): string {
	    return 'The empty unit ' . $this->unit . ' has been liquidated.';
    }

    protected function getData(LemuriaMessage $message): void {
	    parent::getData($message);
	    $this->unit = $message->get();
    }
}
