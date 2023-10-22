<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Travel;

use Lemuria\Engine\Fantasya\Calculus;

interface Trip
{
	public function Calculus(): Calculus;

	public function Capacity(): int;

	public function Knowledge(): int;

	public function Movement(): Movement;

	public function Weight(): int;

	public function Speed(): int;
}
