<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Event;

use JetBrains\PhpStorm\Pure;

use Lemuria\Engine\Fantasya\Action;
use Lemuria\Engine\Fantasya\Message\Party\PartyInRegionsMessage;
use Lemuria\Engine\Fantasya\State;
use Lemuria\Lemuria;
use Lemuria\Model\Catalog;
use Lemuria\Model\Fantasya\Party;
use Lemuria\Model\Fantasya\Party\Census;
use Lemuria\Model\Fantasya\Region;

/**
 * Units that have no health left will die.
 */
final class Visit extends AbstractEvent
{
	#[Pure] public function __construct(State $state) {
		parent::__construct($state, Action::BEFORE);
	}

	protected function run(): void {
		foreach (Lemuria::Catalog()->getAll(Catalog::PARTIES) as $party /* @var Party $party */) {
			Lemuria::Log()->debug('Running Visit for Party ' . $party->Id() . '.', ['party' => $party]);
			$census = new Census($party);
			$atlas  = $census->getAtlas();
			foreach ($atlas as $region /* @var Region $region */) {
				$party->Chronicle()->add($region);
			}
			$this->message(PartyInRegionsMessage::class, $party)->p($atlas->count());
		}
	}
}
