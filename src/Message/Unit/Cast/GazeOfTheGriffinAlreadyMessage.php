<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Message\Unit\Cast;

use Lemuria\Engine\Fantasya\Message\LemuriaMessage;
use Lemuria\Id;

class GazeOfTheGriffinAlreadyMessage extends AbstractCastMessage
{
	protected Id $region;

	protected function create(): string {
		return 'The region ' . $this->region . ' is already known to us.';
	}

	protected function getData(LemuriaMessage $message): void {
		parent::getData($message);
		$this->region = $message->get();
	}
}
