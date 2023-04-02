<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Factory;

use Lemuria\Engine\Fantasya\Calculus;
use Lemuria\Engine\Fantasya\Combat\WeaponSkill;
use Lemuria\Exception\LemuriaException;
use Lemuria\Model\Fantasya\Combat\BattleRow;
use Lemuria\Model\Fantasya\Distribution;
use Lemuria\Model\Fantasya\Factory\InventoryDistribution;
use Lemuria\Model\Fantasya\Quantity;
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

	public function __construct(private readonly Calculus $calculus) {
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
}
