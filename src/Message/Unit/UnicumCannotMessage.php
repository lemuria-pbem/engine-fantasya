<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Message\Unit;

class UnicumCannotMessage extends UnicumNoMaterialMessage
{
	protected function create(): string {
		return 'Unit ' . $this->id . ' cannot create a ' . $this->composition . '.';
	}
}
