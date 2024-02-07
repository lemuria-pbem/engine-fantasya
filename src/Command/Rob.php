<?php
declare (strict_types = 1);
namespace Lemuria\Engine\Fantasya\Command;

use Lemuria\Engine\Fantasya\Message\Region\RobBattleMessage;
use Lemuria\Engine\Fantasya\Message\Unit\RobFailsMessage;
use Lemuria\Engine\Fantasya\Message\Unit\RobLootMessage;
use Lemuria\Engine\Fantasya\Phrase;
use Lemuria\Engine\Fantasya\State;
use Lemuria\Lemuria;
use Lemuria\Model\Fantasya\Quantity;
use Lemuria\Model\Fantasya\Resources;
use Lemuria\Model\Fantasya\Unit;
use Lemuria\SortMode;

/**
 * Rob units without attacking them.
 *
 * If anyone of the attacked units is ready to fight, the robbery fails and an attack is made.
 *
 * - AUSRAUBEN Monster
 * - AUSRAUBEN <race>
 * - AUSRAUBEN <unit>...
 * - AUSRAUBEN Partei <party>...
 */
final class Rob extends AssaultCommand
{
	/**
	 * @var array<Unit>
	 */
	private array $robbed = [];

	protected function run(): void {
		if ($this->context->getTurnOptions()->IsSimulation()) {
			return;
		}

		$region   = $this->unit->Region();
		$campaign = $this->context->getCampaign($region);
		$campaign->mount();
		$i = 0;
		foreach ($campaign->Battles() as $battle) {
			if ($battle->isSurrender()) {
				$region = $battle->Place()->Region();
				Lemuria::Log()->debug('Robbery ' . ++$i . ' in region ' . $region . ' is successful.');
				$remaining = $this->unit->Size();
				foreach ($this->units->sort(SortMode::BySize)->reverse() as $unit) {
					/** @var Unit $unit */
					$size = $unit->Size();
					if ($size > $remaining) {
						$this->rob($unit, $size);
						break;
					}
					$remaining -= $size;
					$this->rob($unit);
				}
				$robber = $this->unit->Party()->Name();
				$this->message(RobBattleMessage::class, $region)->p($robber, RobBattleMessage::ROBBER)->p($this->robbed, RobBattleMessage::VICTIM);
			} else {
				Lemuria::Log()->debug('Robbery attempt' . ++$i . ' in region ' . $battle->Place()->Region() . ' results in combat.');
				$ids = [];
				foreach ($this->units as $unit) {
					$ids[] = (string)$unit->Id();
					$this->message(RobFailsMessage::class, $unit);
				}
				$phrase = new Phrase('ANGREIFEN ' . implode(' ', $ids));
				$attack = new Attack($phrase, $this->context);
				State::getInstance()->injectIntoTurn($attack->from($this->unit));
				$this->message(RobFailsMessage::class);
			}
		}
	}

	protected function commitCommand(UnitCommand $command): void {
		$region                                   = $this->unit->Region();
		self::$resetCampaign[$region->Id()->Id()] = $region;
		parent::commitCommand($command);
	}

	private function rob(Unit $unit, ?int $size = null): void {
		if (!$size) {
			$size = $unit->Size();
		}
		if ($size > 0) {
			$loot          = $this->unit->Party()->Loot();
			$take          = new Resources();
			$inventory     = $unit->Inventory();
			$distributions = $this->context->getCalculus($unit)->inventoryDistribution();
			foreach ($distributions as $distribution) {
				$n     = $distribution->Size();
				$items = $distribution->lose(min($n, $size));
				foreach ($items as $item) {
					$commodity = $item->Commodity();
					if ($loot->wants($commodity)) {
						$inventory->remove($item);
						$take->add(new Quantity($commodity, $item->Count()));
					}
				}
			}
			if (!$take->isEmpty()) {
				$inventory = $this->unit->Inventory();
				foreach ($take as $item) {
					$inventory->add($item);
					$this->message(RobLootMessage::class)->e($unit)->i($item);
				}
				$this->robbed[] = $unit->Name();
			}
		}
	}
}
