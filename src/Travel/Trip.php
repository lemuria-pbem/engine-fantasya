<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Travel;

interface Trip
{
	public function Capacity(): int;

	public function Knowledge(): int;

	public function Movement(): Movement;

	public function Weight(): int;

	public function Speed(): int;
}
