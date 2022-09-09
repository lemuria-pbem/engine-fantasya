<?php
declare (strict_types = 1);
namespace Lemuria\Engine\Fantasya\Command;

use Lemuria\Engine\Fantasya\Exception\UnknownItemException;
use Lemuria\Engine\Fantasya\Factory\GiftTrait;
use Lemuria\Engine\Fantasya\Message\Unit\RegulateNotInsideMessage;
use Lemuria\Engine\Fantasya\Message\Unit\RegulateNotOwnerMessage;
use Lemuria\Exception\SingletonException;
use Lemuria\Model\Fantasya\Building;
use Lemuria\Model\Fantasya\Building\Market;
use Lemuria\Model\Fantasya\Construction;
use Lemuria\Model\Fantasya\Extension\Market as MarketExtension;
use Lemuria\Model\Fantasya\Market\Tradeables;
use Lemuria\SingletonSet;

/**
 * Base command to regulate tradeable goods on the market.
 *
 * - ERLAUBEN|VERBIETEN [Alles]
 * - ERLAUBEN|VERBIETEN Nichts
 * - ERLAUBEN|VERBIETEN <commodity>...
 * - ERLAUBEN|VERBIETEN <kind>...
 */
abstract class RegulateCommand extends UnitCommand
{
	use GiftTrait;

	protected final const NONE = 0;

	protected final const SOME = 1;

	protected final const ALL = 2;

	protected ?Construction $construction = null;

	protected Building $building;

	protected ?MarketExtension $market = null;

	protected Tradeables $tradeables;

	protected bool $isOwner;

	protected int $what;

	protected SingletonSet $commodities;

	protected function initialize(): void {
		$this->construction = $this->unit->Construction();
		if ($this->construction) {
			$this->building = $this->construction->Building();
			$this->isOwner  = $this->unit === $this->construction->Inhabitants()->Owner();
			if ($this->isOwner) {
				if ($this->building instanceof Market) {
					/** @var MarketExtension $market */
					$market           = $this->construction->Extensions()->offsetGet(MarketExtension::class);
					$this->market     = $market;
					$this->tradeables = $market->Tradeables();
				}
			}
		}
	}

	protected function run(): void {
		if (!$this->construction) {
			$this->message(RegulateNotInsideMessage::class);
			return;
		}
		if (!$this->isOwner) {
			$this->message(RegulateNotOwnerMessage::class);
			return;
		}
		if (!$this->market) {
			$this->message(RegulateNotInsideMessage::class);
			return;
		}

		$n = $this->phrase->count();
		if ($n < 1) {
			$this->what = self::ALL;
		} elseif ($n === 1) {
			$this->what = match (strtolower($this->phrase->getParameter())) {
				'nichts' => self::NONE,
				'alles'  => self::ALL,
				default  => self::SOME
			};
		} else {
			$this->what = self::SOME;
		}

		if ($this->what === self::SOME) {
			$this->commodities = new SingletonSet();
			$i = 1;
			try {
				while ($i <= $n) {
					$this->commodities->add($this->parseCommodity($this->phrase->getParameter($i)));
					$i++;
				}
			} catch (SingletonException|UnknownItemException) {
				$this->commodities->add($this->context->Factory()->commodity($this->phrase->getLine($i)));
			}
		}
	}

	protected function isChange(bool $toExclude): bool {
		if ($this->tradeables->clear()->IsExclusion() === $toExclude) {
			return false;
		}
		$this->tradeables->setIsExclusion($toExclude);
		return true;
	}
}
