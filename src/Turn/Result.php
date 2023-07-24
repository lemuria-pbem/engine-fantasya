<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Turn;

use Lemuria\Model\Fantasya\Party;
use Lemuria\Model\Fantasya\People;

final class Result
{
	private ?Party $party = null;

	public function __construct(private readonly People $people) {
	}

	public function Party(): ?Party {
		return $this->party;
	}

	public function Units(): People {
		return $this->people;
	}

	public function setParty(Party $party): Result {
		$this->party = $party;
		return $this;
	}
}
