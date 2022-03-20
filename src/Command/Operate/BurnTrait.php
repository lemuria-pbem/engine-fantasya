<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Command\Operate;

use Lemuria\Engine\Fantasya\Message\Unit\Operate\UnicumDestroyBurnMessage;

trait BurnTrait
{
	protected function destroyMessage(): void {
		$unicum = $this->operator->Unicum();
		$this->message(UnicumDestroyBurnMessage::class, $this->operator->Unit())->s($unicum->Composition())->e($unicum);
	}
}
