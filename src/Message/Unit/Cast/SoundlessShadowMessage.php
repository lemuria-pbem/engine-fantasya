<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Message\Unit\Cast;

use Lemuria\Engine\Fantasya\Message\LemuriaMessage;

class SoundlessShadowMessage extends AbstractCastMessage
{
	protected int $camouflage;

	protected function create(): string {
		return 'Unit ' . $this->id . ' now has Camouflage level ' . $this->camouflage . ' and will sneak past guards.';
	}

	protected function getData(LemuriaMessage $message): void {
		parent::getData($message);
		$this->camouflage = $message->getParameter();
	}
}
