<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Combat;

class Fighter
{
	public int $opponent;

	public int $fighter;

	public function __construct(public int $health) {
	}
}
