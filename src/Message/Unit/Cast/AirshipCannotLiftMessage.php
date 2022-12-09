<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Message\Unit\Cast;

use Lemuria\Engine\Fantasya\Message\LemuriaMessage;
use Lemuria\Engine\Message\Result;
use Lemuria\Id;

class AirshipCannotLiftMessage extends AbstractCastMessage
{
	protected Result $result = Result::Failure;

	protected Id $vessel;

	protected function create(): string {
		return 'Unit ' . $this->id . ' cannot spend enough aura to lift up the vessel ' . $this->vessel . '.';
	}

	protected function getData(LemuriaMessage $message): void {
		parent::getData($message);
		$this->vessel = $message->get();
	}
}
