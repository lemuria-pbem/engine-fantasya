<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Factory\Model;

use Lemuria\Model\Fantasya\Party;
use Lemuria\Model\Fantasya\Region;

class Buzz implements \Stringable
{
	protected ?Party $origin = null;

	protected ?Region $locality = null;

	public function __construct(protected string $rumor) {
	}

	public function Origin(): ?Party {
		return $this->origin;
	}

	public function Locality(): ?Region {
		return $this->locality;
	}

	public function __toString(): string {
		return $this->rumor;
	}

	public function setOrigin(Party $party): static {
		$this->origin = $party;
		return $this;
	}

	public function setLocality(Region $region): static {
		$this->locality = $region;
		return $this;
	}
}
