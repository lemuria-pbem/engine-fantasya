<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Message\Unit;

class GriffineggAttackerMessage extends GriffineggReturnMessage
{
	protected function create(): string {
		return $this->griffins . ' form unit ' . $this->id . ' to attack a unit that attempts to steal eggs.';
	}

	protected function index(): int {
		return $this->griffins->Count() === 1 ? 0 : 2;
	}
}
