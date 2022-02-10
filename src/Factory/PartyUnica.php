<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Factory;

use Lemuria\Engine\Fantasya\Effect\UnicumRead;
use Lemuria\Engine\Fantasya\State;
use Lemuria\Lemuria;
use Lemuria\Model\Fantasya\Party;
use Lemuria\Model\Fantasya\Treasury;

final class PartyUnica
{
	private Treasury $treasury;

	public function __construct(Party $party) {
		$effect         = new UnicumRead(State::getInstance());
		$existing       = Lemuria::Score()->find($effect->setParty($party));
		$this->treasury = $existing instanceof UnicumRead ? $existing->Treasury() : $effect->Treasury();
	}

	public function Treasury(): Treasury {
		return $this->treasury;
	}
}
