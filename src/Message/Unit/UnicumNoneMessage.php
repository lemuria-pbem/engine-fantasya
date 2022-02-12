<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Message\Unit;

class UnicumNoneMessage extends UnicumNoMaterialMessage
{
	protected function create(): string {
		return 'Unit ' . $this->id . ' cannot create any more ' . $this->composition . '.';
	}
}
