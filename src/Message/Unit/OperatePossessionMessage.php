<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Message\Unit;

class OperatePossessionMessage extends OperateNoCompositionMessage
{
	protected function create(): string {
		return 'Unit ' . $this->id . ' must take the ' . $this->composition . ' with ID ' . $this->unicum . ' into possession to use it.';
	}
}
