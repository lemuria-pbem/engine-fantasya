<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Effect;

use JetBrains\PhpStorm\ExpectedValues;
use JetBrains\PhpStorm\Pure;

use Lemuria\Engine\Score;
use Lemuria\Model\Catalog;
use Lemuria\Model\Fantasya\Party;

abstract class AbstractPartyEffect extends AbstractEffect
{
	private ?Party $party = null;

	#[ExpectedValues(valuesFromClass: Catalog::class)]
	#[Pure]
	public function Catalog(): int {
		return Score::PARTY;
	}

	public function Party(): Party {
		if (!$this->party) {
			$this->party = Party::get($this->Id());
		}
		return $this->party;
	}

	public function setParty(Party $party): self {
		$this->party = $party;
		$this->setId($party->Id());
		return $this;
	}
}
