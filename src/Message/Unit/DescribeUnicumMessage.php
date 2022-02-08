<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Message\Unit;

use Lemuria\Engine\Fantasya\Message\LemuriaMessage;
use Lemuria\Engine\Message;
use Lemuria\Id;

class DescribeUnicumMessage extends AbstractUnitMessage
{
	protected string $level = Message::SUCCESS;

	protected Id $unicum;

	protected function create(): string {
		return 'Unicum ' . $this->unicum . ' now has a new description.';
	}

	protected function getData(LemuriaMessage $message): void {
		parent::getData($message);
		$this->unicum = $message->get();
	}
}
