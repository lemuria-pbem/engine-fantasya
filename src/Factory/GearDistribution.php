<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Factory;

use Lemuria\Engine\Fantasya\Calculus;
use Lemuria\Engine\Fantasya\Combat\WeaponSkill;
use Lemuria\Exception\LemuriaException;
use Lemuria\Model\Fantasya\Combat\BattleRow;
use Lemuria\Model\Fantasya\Commodity;
use Lemuria\Model\Fantasya\Distribution;
use Lemuria\Model\Fantasya\Factory\InventoryDistribution;
use Lemuria\Model\Fantasya\Quantity;
use Lemuria\Model\Fantasya\Resources;
use Lemuria\Model\Fantasya\Talent;

class GearDistribution extends InventoryDistribution
{
	/**
	 * @var array<string>
	 */
	protected array $bestSkill = [];

	/**
	 * @var array<string, array>
	 */
	protected array $weaponSkill = [];

	protected Resources $inventory;

	public function __construct(protected Calculus $calculus) {
		parent::__construct($this->calculus->Unit());
	}

	public function distribute(): GearDistribution {
		$this->sortWeaponSkills();
		if ($this->canDistributeInventory()) {
			parent::distribute();
		} else {
			$this->cloneInventory();
			$this->createDistributions();
			$this->distributeInventory();
		}
		return $this;
	}

	protected function sortWeaponSkills(): void {
		$meleeSkills   = [];
		$distantSkills = [];
		foreach ($this->calculus->weaponSkill() as $weaponSkill) {
			if ($weaponSkill->isMelee()) {
				$meleeSkills[] = $weaponSkill;
			} elseif ($weaponSkill->isDistant()) {
				$distantSkills[] = $weaponSkill;
			}
		}
		$isMelee = !in_array($this->unit->BattleRow(), [BattleRow::Defensive, BattleRow::Back]);
		if ($isMelee) {
			$skills = array_merge($meleeSkills, $distantSkills);
		} else {
			$skills = array_merge($distantSkills, $meleeSkills);
		}

		foreach ($skills as $weaponSkill) {
			/** @var WeaponSkill $weaponSkill */
			$weapons = $this->getWeaponsFor($weaponSkill->Skill()->Talent());
			if (!empty($weapons)) {
				$skill                     = $weaponSkill->Skill()->Talent()::class;
				$this->bestSkill[]         = $skill;
				$this->weaponSkill[$skill] = $weapons;
			}
		}
	}

	protected function getWeaponsFor(Talent $skill): array {
		if (!isset(WeaponSkill::WEAPONS[$skill::class])) {
			throw new LemuriaException('Unsupported weapon skill: ' . $skill);
		}
		$weapons   = [];
		$inventory = $this->unit->Inventory();
		foreach (WeaponSkill::WEAPONS[$skill::class] as $weapon) {
			if ($inventory->offsetExists($weapon)) {
				$quantity  = $inventory[$weapon];
				$weapons[] = new Quantity($quantity->Commodity(), $quantity->Count());
			}
		}
		return $weapons;
	}

	protected function createDistributions(): void {
		$this->distributions = [];
		$size                = $this->unit->Size();
		foreach ($this->bestSkill as $skill) {
			foreach ($this->weaponSkill[$skill] as $quantity) {
				/** @var Quantity $quantity */
				$count        = $quantity->Count();
				$distribution = new Distribution();
				if ($count >= $size) {
					$commodity = $quantity->Commodity();
					$distribution->setSize($size);
					$distribution->add(new Quantity($commodity, 1));
					$this->distributions[] = $distribution;
					$this->inventory->remove(new Quantity($commodity, $size));
					$size = 0;
					break 2;
				} else {
					$distribution->setSize($count);
					$distribution->add(new Quantity($quantity->Commodity(), 1));
					$this->distributions[] = $distribution;
					$this->inventory->remove($quantity);
					$size -= $count;
				}
			}
		}
		if ($size > 0) {
			$distribution          = new Distribution();
			$this->distributions[] = $distribution->setSize($size);
		}
	}

	protected function distributeInventory(): void {
		$size = $this->unit->Size();
		foreach ($this->inventory as $quantity) {
			$total   = $quantity->Count();
			$portion = (int)floor($total / $size);
			$this->giveToEverybody($quantity->Commodity(), $portion);
			$rest = $total % $size;
			$this->giveOnlyOne($quantity->Commodity(), $rest);
		}
	}

	private function canDistributeInventory(): bool {
		if (empty($this->bestSkill)) {
			return true;
		}
		$size = $this->unit->Size();
		foreach ($this->weaponSkill[$this->bestSkill[0]] as $quantity) {
			/** @var Quantity $quantity */
			if ($quantity->Count() >= $size) {
				return true;
			}
		}
		return false;
	}

	private function cloneInventory(): void {
		$this->inventory = new Resources();
		foreach ($this->unit->Inventory() as $quantity) {
			$this->inventory->add(new Quantity($quantity->Commodity(), $quantity->Count()));
		}
	}

	private function giveToEverybody(Commodity $commodity, int $count): void {
		if ($count > 0) {
			foreach ($this->distributions as $distribution) {
				$distribution->add(new Quantity($commodity, $count));
			}
		}
	}

	private function giveOnlyOne(Commodity $commodity, int $amount): void {
		$i = 0;
		while ($amount > 0) {
			$distribution = $this->distributions[$i++];
			$size         = $distribution->Size();
			if ($amount < $size) {
				$newDistribution = $this->splitNewDistribution($distribution, $amount);
				$this->insertNewDistribution($newDistribution, $i);
				$distribution->add(new Quantity($commodity, 1));
				break;
			}
			$distribution->add(new Quantity($commodity, 1));
			$amount -= $size;
		}
	}

	private function splitNewDistribution(Distribution $distribution, int $keepSize): Distribution {
		$newDistribution = new Distribution();
		$newSize         = $distribution->Size() - $keepSize;
		$distribution->setSize($keepSize);
		$newDistribution->setSize($newSize);
		foreach ($distribution as $quantity) {
			$newDistribution->add(new Quantity($quantity->Commodity(), $quantity->Count()));
		}
		return $newDistribution;
	}

	private function insertNewDistribution(Distribution $newDistribution, int $index): void {
		if ($index < count($this->distributions)) {
			$rest                = array_splice($this->distributions, $index);
			$this->distributions = array_merge($this->distributions, [$newDistribution], $rest);
		} else {
			$this->distributions[] = $newDistribution;
		}
	}
}
