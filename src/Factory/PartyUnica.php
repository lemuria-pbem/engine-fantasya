<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Factory;

use Lemuria\Engine\Fantasya\Effect\UnicumRead;
use Lemuria\Engine\Fantasya\State;
use Lemuria\Lemuria;
use Lemuria\Model\Fantasya\Party;
use Lemuria\Model\Fantasya\Resources;
use Lemuria\Model\Fantasya\Treasury;
use Lemuria\Model\Fantasya\Unicum;

final class PartyUnica
{
	private Treasury $treasury;

	private UnicumRead $effect;

	public function __construct(Party $party) {
		$this->effect = new UnicumRead(State::getInstance());
		$existing     = Lemuria::Score()->find($this->effect->setParty($party));
		if ($existing instanceof UnicumRead) {
			$this->effect = $existing;
		}
		$this->treasury = $this->effect->Treasury();
	}

	public function Treasury(): Treasury {
		return $this->treasury;
	}

	public function getInventory(Unicum $unicum): Resources {
		return $this->effect->getInventory($unicum);
	}
}
