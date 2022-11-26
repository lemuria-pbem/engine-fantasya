<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Factory\Model;

use Lemuria\Engine\Newcomer;

class SimpleNewcomer implements Newcomer
{
	public function __construct(private string $uuid, private int $creation) {
	}

	public function Uuid(): string {
		return $this->uuid;
	}

	public function Creation(): int {
		return $this->creation;
	}
}
