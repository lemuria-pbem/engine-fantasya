<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Event;

use Lemuria\Engine\Fantasya\Message\Party\PartyInRegionsMessage;
use Lemuria\Engine\Fantasya\Priority;
use Lemuria\Engine\Fantasya\State;
use Lemuria\Lemuria;
use Lemuria\Model\Domain;
use Lemuria\Model\Fantasya\Party;
use Lemuria\Model\Fantasya\Party\Census;
use Lemuria\Model\Fantasya\Region;

/**
 * Record all regions that a party has visited in the turn.
 */
final class Visit extends AbstractEvent
{
	public static function when(int $rounds): string {
		return match(true) {
			$rounds <   -1 => 'ago',
			$rounds === -1 => 'last',
			$rounds ===  1 => 'next',
			$rounds >    1 => 'in',
			default        => 'now'
		};
	}

	public function __construct(State $state) {
		parent::__construct($state, Priority::Before);
	}

	protected function run(): void {
		foreach (Lemuria::Catalog()->getAll(Domain::Party) as $party /* @var Party $party */) {
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
