<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Message\Construction;

class BreedingAtLeastMessage extends BreedingFullMessage
{
	protected function create(): string {
		return 'We need at least two ' . $this->animal . ' on this farm for breeding.';
	}
}
