<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Event\Game;

use Lemuria\Engine\Fantasya\Event\AbstractEvent;
use Lemuria\Engine\Fantasya\Message\Party\ContinentUpheavalMessage;
use Lemuria\Engine\Fantasya\Priority;
use Lemuria\Engine\Fantasya\State;
use Lemuria\Lemuria;
use Lemuria\Model\Domain;
use Lemuria\Model\Fantasya\Party;

/**
 * This event accompanies the integration of a new continent into the world.
 */
final class ContinentUpheaval extends AbstractEvent
{
	public function __construct(State $state) {
		parent::__construct($state, Priority::BEFORE);
	}

	protected function run(): void {
		foreach (Lemuria::Catalog()->getAll(Domain::Party) as $party /* @var Party $party */) {
			$this->message(ContinentUpheavalMessage::class, $party);
		}
	}
}
