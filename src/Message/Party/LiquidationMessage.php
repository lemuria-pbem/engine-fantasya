<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Message\Party;

use Lemuria\Engine\Fantasya\Message\LemuriaMessage;
use Lemuria\Engine\Message\Result;
use Lemuria\Id;

class LiquidationMessage extends AbstractPartyMessage
{
	protected Result $result = Result::SUCCESS;

	protected Id $unit;

    protected function create(): string {
	    return 'The empty unit ' . $this->unit . ' has been liquidated.';
    }

    protected function getData(LemuriaMessage $message): void {
	    parent::getData($message);
	    $this->unit = $message->get();
    }
}
