<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Message\Unit\Cast;

class CivilCommotionMessage extends AbstractCastMessage
{
	protected function create(): string {
		return 'In region ' . $this->id . ' the peasants start a riot.';
	}
}
