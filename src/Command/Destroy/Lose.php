<?php
declare (strict_types = 1);
namespace Lemuria\Engine\Fantasya\Command\Destroy;

use Lemuria\Engine\Fantasya\Command\Operator;
use Lemuria\Engine\Fantasya\Command\UnitCommand;
use Lemuria\Engine\Fantasya\Exception\UnknownCommandException;
use Lemuria\Engine\Fantasya\Factory\GiftTrait;
use Lemuria\Engine\Fantasya\Factory\Model\Everything;
use Lemuria\Engine\Fantasya\Factory\OperateTrait;
use Lemuria\Engine\Fantasya\Factory\ReassignTrait;
use Lemuria\Engine\Fantasya\Factory\SiegeTrait;
use Lemuria\Engine\Fantasya\Message\Unit\LoseEmptyMessage;
use Lemuria\Engine\Fantasya\Message\Unit\LoseEverythingMessage;
use Lemuria\Engine\Fantasya\Message\Unit\LoseSiegeMessage;
use Lemuria\Engine\Fantasya\Message\Unit\LoseToNoneMessage;
use Lemuria\Engine\Fantasya\Message\Unit\LoseMessage;
use Lemuria\Engine\Fantasya\Message\Unit\LoseToUnitMessage;
use Lemuria\Engine\Fantasya\Phrase;
use Lemuria\Exception\IdException;
use Lemuria\Id;
use Lemuria\Model\Domain;
use Lemuria\Model\Fantasya\Commodity;
use Lemuria\Model\Fantasya\Commodity\Peasant;
use Lemuria\Model\Fantasya\Container;
use Lemuria\Model\Fantasya\Practice;
use Lemuria\Model\Fantasya\Quantity;
use Lemuria\Model\Fantasya\Unicum;
use Lemuria\Model\Reassignment;

/**
 * Implementation of command VERLIEREN.
 *
 * The command donates a unit's commodities to random units of other parties and releases its persons to the peasant
 * population of a region.
 *
 * - VERLIEREN
 * - VERLIEREN Alles
 * - VERLIEREN <commodity>
 * - VERLIEREN Person|Personen
 * - VERLIEREN Alles <commodity>
 * - VERLIEREN <amount> <commodity>
 * - VERLIEREN <amount> Person|Personen
 * - VERLIEREN <composition> <Unicum>
 *
 * - GIB 0
 * - GIB 0 Alles
 * - GIB 0 <commodity>
 * - GIB 0 Person|Personen
 * - GIB 0 Alles <commodity>
 * - GIB 0 <amount> <commodity>
 * - GIB 0 <amount> Person|Personen
 * - GIB 0 <composition> <Unicum>
 */
final class Lose extends UnitCommand implements Operator, Reassignment
{
	use GiftTrait;
	use OperateTrait;
	use ReassignTrait;
	use SiegeTrait;

	protected function run(): void {
		$this->parsePhrase($count, $commodity, $unicum);

		if ($unicum) {
			$this->unicum = $unicum;
			$this->createOperate($unicum, Practice::Lose, $this)->lose();
			return;
		}

		$this->parseObject($count, $commodity);

		if ($this->isSieged($this->unit->Construction())) {
			$this->message(LoseSiegeMessage::class);
		} elseif ($this->commodity instanceof Everything) {
			$this->loseEverything();
		} elseif ($this->commodity instanceof Peasant) {
			$this->dismissPeasants();
		} elseif ($this->commodity instanceof Container) {
			$this->commodity->setResources($this->unit->Inventory());
			foreach ($this->commodity->Commodities() as $commodity /** @var Commodity $commodity */) {
				$this->lose($commodity);
			}
		} else {
			$this->lose($this->commodity);
		}
	}

	protected function checkReassignmentDomain(Domain $domain): bool {
		return $domain === Domain::Unicum;
	}

	protected function getReassignPhrase(string $old, string $new): ?Phrase {
		return $this->hasUnicum() ? $this->getReassignPhraseForParameter($this->phrase->count(), $old, $new) : null;
	}

	private function parsePhrase(string &$count, string &$commodity, ?Unicum &$unicum): void {
		$p = 1;
		$count = $this->phrase->getParameter($p++);
		if ($this->phrase->getVerb() === 'GIB') {
			if (strtolower($count) !== '0') {
				throw new UnknownCommandException($this);
			}
			$count = $this->phrase->getParameter($p++);
		}
		$commodity = $this->phrase->getLine($p);
		$unicum    = $this->parseUnicum($count, $commodity);
	}

	private function hasUnicum(): bool {
		try {
			$this->parsePhrase($count, $commodity, $unicum);
			return $unicum instanceof Unicum;
		} catch (UnknownCommandException) {
			return false;
		}
	}

	private function loseEverything(): void {
		$inventory = $this->unit->Inventory();
		$i         = $inventory->count();
		if ($i > 0) {
			$unit = null;
			foreach ($inventory as $quantity) {
				$unit = $this->giftToRandomUnit($quantity);
				if ($unit) {
					$this->message(LoseToUnitMessage::class, $unit)->e($this->unit)->i($quantity);
				}
			}
			$inventory->clear();
			if (!$unit) {
				$this->message(LoseToNoneMessage::class);
			}
		}

		$s = $this->unit->Size();
		if ($s > 0) {
			$this->peasantsToRegion($s);
			$this->unit->setSize(0);
		}

		if ($i + $s > 0) {
			$this->message(LoseEverythingMessage::class);
		} else {
			$this->message(LoseEmptyMessage::class);
		}
	}

	private function lose(Commodity $commodity): void {
		$quantity = new Quantity($commodity, $this->amount);
		$unit     = $this->giftToRandomUnit($quantity);
		if ($unit) {
			$this->message(LoseToUnitMessage::class, $unit)->e($this->unit)->i($quantity);
		} else {
			$this->message(LoseMessage::class)->i($quantity);
		}
	}

	private function parseUnicum(string $first, string $second): ?Unicum {
		$unicum = null;
		try {
			$treasury = $this->unit->Treasury();
			$factory  = $this->context->Factory();
			if ($first) {
				if ($second) {
					if ($factory->isComposition($first)) {
						$composition = $factory->composition($first);
						$id          = Id::fromId($second);
						if ($treasury->has($id)) {
							$unicum = $treasury[$id];
							if ($unicum->Composition() !== $composition) {
								$unicum = null;
							}
						}
					}
				} else {
					$id = Id::fromId($first);
					if ($treasury->has($id)) {
						$unicum = $treasury[$id];
					}
				}
			}
		} catch (IdException) {
		}
		return $unicum;
	}
}
