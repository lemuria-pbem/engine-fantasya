<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Effect;

use Lemuria\Model\Domain;
use Lemuria\Model\Fantasya\Party;

abstract class AbstractPartyEffect extends AbstractEffect
{
	private ?Party $party = null;

	public function Catalog(): Domain {
		return Domain::PARTY;
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
