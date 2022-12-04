<?php
declare (strict_types = 1);
namespace Lemuria\Engine\Fantasya\Command\Create;

use function Lemuria\getClass;
use Lemuria\Engine\Fantasya\Command\Trespass\Enter;
use Lemuria\Engine\Fantasya\Effect\DecayEffect;
use Lemuria\Engine\Fantasya\Effect\SignpostEffect;
use Lemuria\Engine\Fantasya\Exception\InvalidCommandException;
use Lemuria\Engine\Fantasya\Factory\MarketBuilder;
use Lemuria\Engine\Fantasya\Factory\Model\AnyBuilding;
use Lemuria\Engine\Fantasya\Factory\Model\AnyCastle;
use Lemuria\Engine\Fantasya\Factory\Model\Job;
use Lemuria\Engine\Fantasya\Message\Unit\ConstructionBuildMessage;
use Lemuria\Engine\Fantasya\Message\Unit\ConstructionCreateMessage;
use Lemuria\Engine\Fantasya\Message\Unit\ConstructionDependencyMessage;
use Lemuria\Engine\Fantasya\Message\Unit\ConstructionExperienceMessage;
use Lemuria\Engine\Fantasya\Message\Unit\ConstructionMessage;
use Lemuria\Engine\Fantasya\Message\Unit\ConstructionOnlyMessage;
use Lemuria\Engine\Fantasya\Message\Unit\ConstructionResourcesMessage;
use Lemuria\Engine\Fantasya\Message\Unit\ConstructionUnableMessage;
use Lemuria\Engine\Fantasya\Message\Unit\LeaveConstructionMessage;
use Lemuria\Engine\Fantasya\State;
use Lemuria\Exception\LemuriaException;
use Lemuria\Id;
use Lemuria\Lemuria;
use Lemuria\Model\Dictionary;
use Lemuria\Model\Domain;
use Lemuria\Model\Fantasya\Building;
use Lemuria\Model\Fantasya\Building\AbstractCastle;
use Lemuria\Model\Fantasya\Building\Castle;
use Lemuria\Model\Fantasya\Building\Market;
use Lemuria\Model\Fantasya\Building\Monument;
use Lemuria\Model\Fantasya\Building\Ruin;
use Lemuria\Model\Fantasya\Building\Signpost;
use Lemuria\Model\Fantasya\Building\Site;
use Lemuria\Model\Fantasya\Construction as ConstructionModel;
use Lemuria\Model\Fantasya\Extension\Market as MarketExtension;
use Lemuria\Model\Fantasya\Quantity;
use Lemuria\Model\Fantasya\Requirement;

/**
 * Implementation of command MACHEN <Gebäude> (create construction).
 *
 * The command lets units build constructions. If the unit is inside a construction, that construction is built further.
 *
 * - MACHEN Burg|Gebäude|Gebaeude
 * - MACHEN Burg|Gebäude|Gebaeude <size>
 * - MACHEN <Building>
 * - MACHEN <Building> <size>
 */
final class Construction extends AbstractProduct
{
	private int $size;

	private bool $hasMarket = false;

	private ?ConstructionModel $fromOutside;

	protected function initialize(): void {
		$this->fromOutside = $this->prepareBuildingFromOutside();
		$this->replacePlaceholderJob();
		parent::initialize();
		$castle = $this->context->getIntelligence($this->unit->Region())->getGovernment();
		if ($castle?->Size() > Site::MAX_SIZE) {
			$this->hasMarket = true;
		}
	}

	protected function run(): void {
		$building = $this->getBuilding();
		if (!$this->checkDependency($building)) {
			$dependency = $building->Dependency();
			$this->message(ConstructionDependencyMessage::class)->s($building)->s($dependency, ConstructionDependencyMessage::DEPENDENCY);
			return;
		}

		$construction     = $this->leaveCurrentConstructionFor($building);
		$this->size       = $construction?->Size() ?? 0;
		$demand           = $this->job->Count();
		$talent           = $building->getCraft()->Talent();
		$this->capability = $this->calculateProduction($building->getCraft());
		$reserve          = $this->calculateResources($building->getMaterial());
		$production       = min($this->capability, $reserve);
		if ($production > 0) {
			$yield = min($production, $demand);
			foreach ($building->getMaterial() as $quantity /* @var Quantity $quantity */) {
				$count       = (int)ceil($this->consumption * $yield * $quantity->Count());
				$consumption = new Quantity($quantity->Commodity(), $count);
				$this->unit->Inventory()->remove($consumption);
			}

			if ($construction) {
				$construction->setSize($construction->Size() + $yield);
				if ($this->job->hasCount() && $demand > $production && $demand < PHP_INT_MAX) {
					$this->message(ConstructionOnlyMessage::class)->e($construction)->p($yield);
				} else {
					$this->message(ConstructionBuildMessage::class)->e($construction)->p($yield);
				}
			} else {
				$id           = Lemuria::Catalog()->nextId(Domain::CONSTRUCTION);
				$construction = new ConstructionModel();
				$dictionary   = new Dictionary();
				$name         = $dictionary->get('building.' . getClass($building)) . ' ' . $id;
				$construction->setName($name)->setId($id);
				$construction->Inhabitants()->add($this->unit);
				$this->unit->Region()->Estate()->add($construction);
				$construction->setBuilding($building)->setSize($yield);
				if ($this->job->hasCount() && $demand > $production && $demand < PHP_INT_MAX) {
					$this->message(ConstructionOnlyMessage::class)->e($construction)->p($yield);
				} else {
					$this->message(ConstructionMessage::class)->s($construction->Building());
				}
			}
			$this->addToWorkload($yield);
			$this->initializeMarket($construction);
			$this->addConstructionExtensions($construction);
			$this->addConstructionEffects($construction);
		} else {
			if ($this->capability > 0) {
				if ($construction) {
					$this->message(ConstructionResourcesMessage::class)->e($construction);
				} else {
					$this->message(ConstructionCreateMessage::class)->s($building);
				}
			} else {
				if ($construction) {
					$this->message(ConstructionExperienceMessage::class)->e($construction)->s($talent);
				} else {
					$this->message(ConstructionUnableMessage::class)->s($building)->s($talent, ConstructionUnableMessage::TALENT);
				}
			}
		}
	}

	protected function checkDependency(Building $building): bool {
		$dependency = $building->Dependency();
		if ($dependency) {
			if ($dependency instanceof Castle) {
				$isCastle = true;
				$minSize  = $dependency->MinSize();
			} else {
				$isCastle = false;
				$minSize  = 0;
			}
			foreach ($this->unit->Region()->Estate() as $construction /* @var ConstructionModel $construction */) {
				if ($isCastle) {
					if ($construction->Building() instanceof Castle && $construction->Size() >= $minSize) {
						if ($construction->Inhabitants()->Owner()?->Party() === $this->unit->Party()) {
							return true;
						}
					}
				} else {
					if ($construction->Building() === $dependency) {
						if ($construction->Inhabitants()->Owner()?->Party() === $this->unit->Party()) {
							return true;
						}
					}
				}
			}
			return false;
		}
		return true;
	}

	/**
	 * Get maximum amount that can be produced by knowledge.
	 */
	protected function calculateProduction(Requirement $craft): int {
		if ($this->job->getObject() instanceof Castle) {
			$production = $this->calculateCastleProduction($this->size);
			$this->reduceByWorkload($production);
			return $production;
		}
		return parent::calculateProduction($craft);
	}

	private function prepareBuildingFromOutside(): ?ConstructionModel {
		$building = $this->job->getObject();
		if ($building::class === AnyBuilding::class) {
			$knowledge = $this->unit->Knowledge();
			$ability   = $knowledge[$building->getCraft()->Talent()];
			if ($ability->Count() > 0 && $this->phrase->count() === 2) {
				$id     = Id::fromId($this->phrase->getParameter(2));
				$estate = $this->unit->Region()->Estate();
				if ($estate->has($id)) {
					/** @var ConstructionModel $construction */
					$construction = $estate[$id];
					$building     = $construction->Building();
					if (!in_array($building::class, Enter::FORBIDDEN)) {
						return $construction;
					}
				}
			}
		}
		return null;
	}

	private function replacePlaceholderJob(): void {
		$building = $this->job->getObject();
		if ($building instanceof Ruin) {
			throw new InvalidCommandException($this);
		}

		if ($building instanceof AnyCastle) {
			$building = $this->unit->Construction()?->Building();
			if ($building) {
				$this->job = new Job($building, $this->job->Count());
			} else {
				$this->job = new Job(self::createBuilding(Site::class), $this->job->Count());
			}
		} elseif ($building instanceof AnyBuilding) {
			$building = $this->unit->Construction()?->Building();
			if ($building) {
				$this->job = new Job($building, $this->job->Count());
				return;
			}
			if ($this->fromOutside) {
				if (!$this->unit->Construction() && $this->fromOutside->Inhabitants()->isEmpty()) {
					$this->job = new Job($this->fromOutside->Building(), $this->job->Count());
					$this->fromOutside->Inhabitants()->add($this->unit);
					return;
				}
			}
			throw new InvalidCommandException($this);
		}
	}

	private function calculateCastleProduction(int $size, int $pointsUsed = 0): int {
		$castle = AbstractCastle::forSize($size);
		$craft  = $castle->getCraft();
		$cost   = $craft->Level();
		$level  = $this->getProductivity($craft)->Level();
		if ($level < $cost) {
			return 0;
		}

		$unitSize   = $this->unit->Size();
		$points     = (int)floor($this->potionBoost($unitSize) * $unitSize * $level) - $pointsUsed;
		$production = (int)floor($points / $cost);
		$newSize    = $size + $production;
		$maxSize    = $castle->MaxSize();
		if ($newSize <= $maxSize) {
			return $production;
		}

		$newSize     = $maxSize;
		$production  = $newSize - $size;
		$delta       = $production * $cost;
		$points     -= $delta;
		$pointsUsed += $delta;
		$castle      = $castle->Upgrade();
		$cost        = $castle->getCraft()->Level();
		if ($level < $cost || $points < $cost) {
			return $production;
		}

		$production++;
		$newSize++;
		$pointsUsed += $cost;
		return $production + $this->calculateCastleProduction($newSize, $pointsUsed);
	}

	private function getBuilding(): Building {
		$resource = $this->job->getObject();
		if ($resource instanceof Building) {
			return $resource;
		}
		throw new LemuriaException('Expected a building resource.');
	}

	private function leaveCurrentConstructionFor(Building $building): ?ConstructionModel {
		$construction = $this->unit->Construction();
		if ($construction && $construction->Building() !== $building) {
			$construction->Inhabitants()->remove($this->unit);
			$this->message(LeaveConstructionMessage::class)->e($construction);
		}
		return $construction;
	}

	private function initializeMarket(ConstructionModel $construction): void {
		if ($this->hasMarket) {
			return;
		}
		if ($construction->Building() instanceof Castle && $construction->Size() > Site::MAX_SIZE) {
			$region = $construction->Region();
			if ($region->Luxuries()) {
				$marketBuilder = new MarketBuilder($this->context->getIntelligence($region));
				$marketBuilder->initPrices();
				Lemuria::Log()->debug('Market opens the first time in region ' . $region . ' - prices have been initialized.');
			} else {
				Lemuria::Log()->debug('Region ' . $region . ' produces no luxuries - no prices initialized.');
			}
		}
	}

	private function addConstructionExtensions(ConstructionModel $construction): void {
		$building = $construction->Building();
		if ($building instanceof Market) {
			$extensions = $construction->Extensions();
			if (!$extensions->offsetExists(MarketExtension::class)) {
				$extensions->add(new MarketExtension());
			}
		}
	}

	private function addConstructionEffects(ConstructionModel $construction): void {
		$building = $construction->Building();
		if ($building instanceof Monument) {
			$effect = $this->monumentEffect($construction);
			Lemuria::Score()->add($effect->resetAge());
		} elseif ($building instanceof Signpost) {
			$effect = $this->signpostEffect($construction);
			Lemuria::Score()->add($effect->resetAge());
		}
	}

	private function monumentEffect(ConstructionModel $signpost): DecayEffect {
		$effect = new DecayEffect(State::getInstance());
		/** @var DecayEffect $monumentEffect */
		$monumentEffect = Lemuria::Score()->find($effect->setConstruction($signpost)->setInterval(DecayEffect::MONUMENT));
		return $monumentEffect ?? $effect;
	}

	private function signpostEffect(ConstructionModel $signpost): SignpostEffect {
		$effect = new SignpostEffect(State::getInstance());
		/** @var SignpostEffect $signpostEffect */
		$signpostEffect = Lemuria::Score()->find($effect->setConstruction($signpost));
		return $signpostEffect ?? $effect;
	}
}
