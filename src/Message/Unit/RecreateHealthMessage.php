<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Message\Unit;

class RecreateHealthMessage extends RecreateAuraMessage
{
	protected function create(): string {
		return 'Unit ' . $this->id . ' regains ' . $this->points . ' hitpoints.';
	}
}
