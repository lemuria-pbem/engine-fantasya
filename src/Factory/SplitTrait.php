<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Factory;

use Lemuria\Exception\LemuriaException;
use Lemuria\Lemuria;
use Lemuria\Model\Domain;
use Lemuria\Model\Fantasya\Ability;
use Lemuria\Model\Fantasya\Commodity;
use Lemuria\Model\Fantasya\Quantity;
use Lemuria\Model\Fantasya\Resources;
use Lemuria\Model\Fantasya\Unit;

trait SplitTrait
{
	protected function splitUnit(Unit $unit, int $size): Unit {
		$race  = $unit->Race();
		$split = new Unit();
		$split->setId(Lemuria::Catalog()->nextId(Domain::Unit));
		$split->setRace($unit->Race())->setSize($size);
		$split->setName($unit->Name())->setDescription($unit->Description());
		$split->setHealth($unit->Health())->setBattleRow($unit->BattleRow());
		$split->setIsHiding($unit->IsHiding())->setDisguise($unit->Disguise());
		$split->setIsGuarding($unit->IsGuarding())->setIsLooting($unit->IsLooting());
		if ($unit->Aura()) {
			$split->setAura($unit->Aura());
		}
		foreach ($unit->Knowledge() as $ability) {
			$split->Knowledge()->add(new Ability($ability->Talent(), $ability->Experience()));
		}
		$battleSpells = $unit->BattleSpells();
		if ($battleSpells) {
			foreach ($battleSpells as $spell) {
				$split->BattleSpells()->add($spell);
			}
		}


		$unit->Party()->People()->add($split);
		$unit->Region()->Residents()->add($split);
		Lemuria::Log()->debug('A new unit of ' . $size . ' ' . $race . ' has been split from unit ' . $unit . '.');
		return $split;
	}

	protected function removeExcessPayload(Unit $unit, int $maxPayload): Resources {
		$size      = $unit->Size();
		$payload   = (int)floor($maxPayload / $size);
		$inventory = $unit->Inventory();
		$total     = 0;
		$goods     = [];
		foreach ($inventory as $quantity) {
			$weight = $quantity->Commodity()->Weight();
			if (!isset($goods[$weight])) {
				$goods[$weight] = [];
			}
			$goods[$weight][] = $quantity;
			$total += $quantity->Weight();
		}
		krsort($goods);

		$excess     = new Resources();
		$distribute = [];
		if ($total > $maxPayload) {
			foreach ($goods as $weight => $items) {
				foreach ($items as $quantity /** @var Quantity $quantity */) {
					if ($weight > $payload) {
						$inventory->remove(new Quantity($quantity->Commodity(), $quantity->Count()));
						$excess->add(new Quantity($quantity->Commodity(), $quantity->Count()));
						$total -= $quantity->Weight();
					} else {
						$distribute[] = $quantity;
					}
				}
			}
		}
		$distribute = array_reverse($distribute);

		$rest = new Resources();
		while ($total > $maxPayload && !empty($distribute)) {
			$quantity = array_pop($distribute);
			if ($quantity->Count() > $size) {
				$move = new Quantity($quantity->Commodity(), $size - $quantity->Count());
				$inventory->remove($move);
				$excess->add(new Quantity($quantity->Commodity(), $move->Count()));
				$total -= $move->Weight();
				$quantity = new Quantity($quantity->Commodity(), $size);
			}
			$rest->add($quantity);
		}
		$rest->rewind();

		/** @var Commodity $commodity */
		$commodity = null;
		$count     = 0;
		while ($total > $maxPayload) {
			if ($commodity) {
				$inventory->remove(new Quantity($commodity, 1));
				$excess->add(new Quantity($commodity, 1));
				$total -= $commodity->Weight();
				$count--;
				if ($count <= 0) {
					$commodity = null;
				}
			} else {
				if (!$rest->valid()) {
					throw new LemuriaException('Unexpected end of remaining inventory, this should not happen.');
				}
				$quantity  = $rest->current();
				$commodity = $quantity->Commodity();
				$count     = $quantity->Count();
				$rest->next();
			}
		}

		return $excess;
	}
}
