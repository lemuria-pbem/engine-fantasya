<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Command\Create;

use Lemuria\Engine\Fantasya\Activity;
use Lemuria\Engine\Fantasya\Command\UnitCommand;
use Lemuria\Engine\Fantasya\Context;
use Lemuria\Engine\Fantasya\Exception\UnknownCommandException;
use Lemuria\Engine\Fantasya\Factory\DefaultActivityTrait;
use Lemuria\Engine\Fantasya\Factory\WorkloadTrait;
use Lemuria\Engine\Fantasya\Message\Unit\RoadAlreadyCompletedMessage;
use Lemuria\Engine\Fantasya\Message\Unit\RoadCompletedMessage;
use Lemuria\Engine\Fantasya\Message\Unit\RoadExperienceMessage;
use Lemuria\Engine\Fantasya\Message\Unit\RoadInOceanMessage;
use Lemuria\Engine\Fantasya\Message\Unit\RoadMessage;
use Lemuria\Engine\Fantasya\Message\Unit\RoadNoRessourcesMessage;
use Lemuria\Engine\Fantasya\Message\Unit\RoadOnlyMessage;
use Lemuria\Engine\Fantasya\Phrase;
use Lemuria\Model\Fantasya\Commodity;
use Lemuria\Model\Fantasya\Commodity\Stone;
use Lemuria\Model\Fantasya\Quantity;
use Lemuria\Model\Fantasya\Roads;
use Lemuria\Model\Fantasya\Talent;
use Lemuria\Model\Fantasya\Talent\Roadmaking;

/**
 * Build a road.
 *
 * - MACHEN Straße|Strasse <direction> [<amount>]
 */
final class Road extends UnitCommand implements Activity
{
	use DefaultActivityTrait;
	use WorkloadTrait;

	private Talent $roadmaking;

	private Commodity $stone;

	private int $maximum;

	public function __construct(Phrase $phrase, Context $context) {
		parent::__construct($phrase, $context);
		$this->roadmaking = self::createTalent(Roadmaking::class);
		$this->stone      = self::createCommodity(Stone::class);
		$level            = $this->calculus()->knowledge($this->roadmaking)->Level();
		$this->maximum    = $this->unit->Size() * $level;
		$this->initWorkload($this->maximum);
	}

	protected function run(): void {
		throw new UnknownCommandException($this); //TODO
		$n = $this->phrase->count();
		if ($n < 2 || $n > 3) {
			throw new UnknownCommandException($this);
		}

		$region     = $this->unit->Region();
		$landscape  = $region->Landscape();
		$roadStones = $landscape->RoadStones();
		if ($roadStones < 0) {
			$this->message(RoadInOceanMessage::class)->e($region);
			return;
		}

		$param     = $this->phrase->getParameter(2);
		$direction = $this->context->Factory()->direction($param);
		if ($n === 2) {
			$amount = $this->maximum;
		} else {
			$amount = (int)$this->phrase->getParameter(3);
		}
		$amount = $this->reduceByWorkload($amount);
		if ($amount <= 0) {
			$this->message(RoadExperienceMessage::class)->e($region)->s($this->roadmaking);
			return;
		}

		$roads      = $region->Roads();
		$completion = $roads ? $roads[$direction] : 0.0;
		$stones     = (int)round($completion * $roadStones);
		$remaining  = $roadStones - $stones;
		if ($remaining <= 0) {
			$this->message(RoadAlreadyCompletedMessage::class)->e($region)->p($direction);
			return;
		}
		$amount = min($amount, $remaining);

		$reserve = $this->context->getResourcePool($this->unit)->reserve($this->unit, new Quantity($this->stone, $amount));
		$built   = $reserve->Count();
		if ($built > 0) {
			if (!$roads) {
				$roads = new Roads();
				$region->setRoads($roads);
			}
			$this->unit->Inventory()->remove($reserve);
			$stones += $built;
			$this->workload->add($built);
			if ($stones < $roadStones) {
				$roads[$direction] = $stones / $roadStones;
				if ($built < $this->maximum) {
					$this->message(RoadOnlyMessage::class)->e($region)->p($direction)->i($reserve);
				} else {
					$this->message(RoadMessage::class)->e($region)->p($direction)->i($reserve);
				}
			} else {
				$roads[$direction] = 1.0;
				$this->message(RoadCompletedMessage::class)->e($region)->p($direction)->i($reserve);
			}
		} else {
			$this->message(RoadNoRessourcesMessage::class)->e($region)->p($direction);
		}
	}
}
