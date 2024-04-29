<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Factory\Model;

use Lemuria\Model\Fantasya\Party;

class Buzz implements \Stringable
{
	protected ?Party $origin = null;

	public function __construct(protected string $rumor) {
	}

	public function Origin(): ?Party {
		return $this->origin;
	}

	public function __toString(): string {
		return $this->rumor;
	}

	public function setOrigin(Party $party): static {
		$this->origin = $party;
		return $this;
	}
}
