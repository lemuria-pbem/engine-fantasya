<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Command;

use Lemuria\Engine\Fantasya\Activity;
use Lemuria\Engine\Fantasya\Census;
use Lemuria\Engine\Fantasya\Context;
use Lemuria\Engine\Fantasya\Exception\UnknownCommandException;
use Lemuria\Engine\Fantasya\Factory\OneActivityTrait;
use Lemuria\Engine\Fantasya\Message\Party\StealRevealedMessage;
use Lemuria\Engine\Fantasya\Message\Unit\StealDiscoveredMessage;
use Lemuria\Engine\Fantasya\Message\Unit\StealMessage;
use Lemuria\Engine\Fantasya\Message\Unit\StealNotHereMessage;
use Lemuria\Engine\Fantasya\Message\Unit\StealNothingMessage;
use Lemuria\Engine\Fantasya\Message\Unit\StealOnlyMessage;
use Lemuria\Engine\Fantasya\Message\Unit\StealOwnUnitMessage;
use Lemuria\Engine\Fantasya\Outlook;
use Lemuria\Engine\Fantasya\Phrase;
use Lemuria\Model\Fantasya\Commodity;
use Lemuria\Model\Fantasya\Commodity\Silver;
use Lemuria\Model\Fantasya\Factory\BuilderTrait;
use Lemuria\Model\Fantasya\Quantity;
use Lemuria\Model\Fantasya\Talent\Camouflage;
use Lemuria\Model\Fantasya\Talent\Perception;

/**
 * Implementation of STEHLEN (steal).
 *
 * - STEHLEN <unit>
 */
final class Steal extends UnitCommand implements Activity
{
	use BuilderTrait;
	use OneActivityTrait;

	private const SILVER = 50;

	private Commodity $silver;

	public function __construct(Phrase $phrase, Context $context) {
		parent::__construct($phrase, $context);
		$this->silver = self::createCommodity(Silver::class);
	}

	protected function run(): void {
		if ($this->phrase->count() !== 1) {
			throw new UnknownCommandException($this);
		}
		$i    = 1;
		$unit = $this->nextId($i);
		$region = $this->unit->Region();
		if ($unit->Region() !== $region || !$this->calculus()->canDiscover($unit)) {
			$this->message(StealNotHereMessage::class)->e($unit);
			return;
		}

		$party = $unit->Party();
		if ($party === $this->unit->Party()) {
			$this->message(StealOwnUnitMessage::class)->e($unit);
			return;
		}

		if ($this->context->getTurnOptions()->IsSimulation()) {
			$this->message(StealNothingMessage::class)->e($unit);
		}

		$outlook = new Outlook(new Census($party));
		if ($outlook->getApparitions($region)->has($this->unit->Id())) {
			$this->message(StealDiscoveredMessage::class)->e($unit);
			$this->message(StealRevealedMessage::class, $party)->e($region)->e($this->unit, StealRevealedMessage::UNIT);
			return;
		}

		$camouflage = $this->calculus()->knowledge(Camouflage::class)->Level();
		$perception = $this->context->getCalculus($unit)->knowledge(Perception::class)->Level();
		$silver     = ($camouflage - $perception) * self::SILVER * $this->unit->Size();
		$inventory  = $unit->Inventory();
		$available  = $inventory[$this->silver]->Count();
		$pickings   = min($available, $silver);
		if ($pickings > 0) {
			$quantity = new Quantity($this->silver, $pickings);
			$inventory->remove($quantity);
			$this->unit->Inventory()->add($quantity);
			if ($available < $silver) {
				$this->message(StealOnlyMessage::class)->e($unit)->i($quantity);
			} else {
				$this->message(StealMessage::class)->e($unit)->i($quantity);
			}
		} else {
			$this->message(StealNothingMessage::class)->e($unit);
		}
	}
}
