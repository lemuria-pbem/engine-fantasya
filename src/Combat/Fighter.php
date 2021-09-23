<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Combat;

use Lemuria\Model\Fantasya\Potion;

class Fighter
{
	public int $opponent;

	public int $fighter;

	public ?Potion $potion = null;

	public function __construct(public int $health) {
	}
}
