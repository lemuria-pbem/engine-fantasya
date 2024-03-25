<?php
declare (strict_types = 1);
namespace Lemuria\Engine\Fantasya\Command\Create;

use function Lemuria\getClass;
use Lemuria\Engine\Fantasya\Activity;
use Lemuria\Engine\Fantasya\Effect\DecayEffect;
use Lemuria\Engine\Fantasya\Effect\SignpostEffect;
use Lemuria\Engine\Fantasya\Exception\InvalidCommandException;
use Lemuria\Engine\Fantasya\Factory\GrammarTrait;
use Lemuria\Engine\Fantasya\Factory\MarketBuilder;
use Lemuria\Engine\Fantasya\Factory\Model\AnyBuilding;
use Lemuria\Engine\Fantasya\Factory\Model\AnyCastle;
use Lemuria\Engine\Fantasya\Factory\Model\Job;
use Lemuria\Engine\Fantasya\Factory\ModifiedActivityTrait;
use Lemuria\Engine\Fantasya\Message\Casus;
use Lemuria\Engine\Fantasya\Message\Unit\ConstructionAnyMessage;
use Lemuria\Engine\Fantasya\Message\Unit\ConstructionBuildMessage;
use Lemuria\Engine\Fantasya\Message\Unit\ConstructionCreateMessage;
use Lemuria\Engine\Fantasya\Message\Unit\ConstructionDependencyMessage;
use Lemuria\Engine\Fantasya\Message\Unit\ConstructionExperienceMessage;
use Lemuria\Engine\Fantasya\Message\Unit\ConstructionFarmMessage;
use Lemuria\Engine\Fantasya\Message\Unit\ConstructionMessage;
use Lemuria\Engine\Fantasya\Message\Unit\ConstructionOnlyMessage;
use Lemuria\Engine\Fantasya\Message\Unit\ConstructionResourcesMessage;
use Lemuria\Engine\Fantasya\Message\Unit\ConstructionSizeMessage;
use Lemuria\Engine\Fantasya\Message\Unit\ConstructionUnableMessage;
use Lemuria\Engine\Fantasya\Message\Unit\LeaveConstructionMessage;
use Lemuria\Engine\Fantasya\Phrase;
use Lemuria\Engine\Fantasya\State;
use Lemuria\Exception\LemuriaException;
use Lemuria\Lemuria;
use Lemuria\Model\Domain;
use Lemuria\Model\Fantasya\Building;
use Lemuria\Model\Fantasya\Building\AbstractCastle;
use Lemuria\Model\Fantasya\Building\AbstractVenue;
use Lemuria\Model\Fantasya\Building\Canal;
use Lemuria\Model\Fantasya\Building\Castle;
use Lemuria\Model\Fantasya\Building\Farm;
use Lemuria\Model\Fantasya\Building\Market;
use Lemuria\Model\Fantasya\Building\Monument;
use Lemuria\Model\Fantasya\Building\Port;
use Lemuria\Model\Fantasya\Building\Signpost;
use Lemuria\Model\Fantasya\Building\Site;
use Lemuria\Model\Fantasya\Construction as ConstructionModel;
use Lemuria\Model\Fantasya\Extension\Duty;
use Lemuria\Model\Fantasya\Extension\Fee;
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
 * - MACHEN Gebäude <ID>
 * - MACHEN Gebäude <ID> <size>
 */
final class Construction extends AbstractProduct
{
	use GrammarTrait;
	use ModifiedActivityTrait;

	/**
	 * @type array<string>
	 */
	private const array FORBIDDEN = [Signpost::class];

	/**
	 * @type array<string, array<string>>
	 */
	private const array EXTENSIONS = [
		Canal::class  => [Fee::class],
		Market::class => [MarketExtension::class],
		Port::class   => [Fee::class, Duty::class]
	];

	private int $size;

	private int $remainingSize = PHP_INT_MAX;

	private bool $hasMarket = false;

	private ?ConstructionModel $fromOutside;

	public function allows(Activity $activity): bool {
		return $this->job->Count() !== PHP_INT_MAX && getClass($activity) === getClass($this);
	}

	protected function initialize(): void {
		$this->fromOutside = $this->prepareBuildingFromOutside();
		$this->replacePlaceholderJob();
		parent::initialize();
		$castle = $this->context->getIntelligence($this->unit->Region())->getCastle();
		if ($castle?->Size() > Site::MAX_SIZE) {
			$this->hasMarket = true;
		}
	}

	protected function run(): void {
		if ($this->job->getObject() instanceof AnyBuilding && !$this->unit->Construction()) {
			$this->message(ConstructionAnyMessage::class);
			return;
		}

		$building = $this->getBuilding();
		if (!$this->checkDependency($building)) {
			$dependency = $building->Dependency();
			$this->message(ConstructionDependencyMessage::class)->s($building)->s($dependency, ConstructionDependencyMessage::DEPENDENCY);
			return;
		}
		if ($building instanceof Farm) {
			$landscapes = $building->Landscapes();
			$landscape  = $this->unit->Region()->Landscape();
			if (!isset($landscapes[$landscape])) {
				$this->message(ConstructionFarmMessage::class)->s($building)->s($landscape, ConstructionFarmMessage::LANDSCAPE);
				return;
			}
		}

		$construction = $this->leaveCurrentConstructionFor($building);
		$this->size   = $construction?->Size() ?? 0;
		$this->calculateRemainingSize();
		$demand           = $this->job->Count();
		$talent           = $building->getCraft()->Talent();
		$this->capability = $this->calculateProduction($building->getCraft());
		$reserve          = $this->calculateResources($building->getMaterial());
		$production       = min($this->capability, $reserve, $this->remainingSize);
		if ($production > 0) {
			$yield = min($production, $demand);
			foreach ($building->getMaterial() as $quantity) {
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
				$id           = Lemuria::Catalog()->nextId(Domain::Construction);
				$construction = new ConstructionModel();
				$this->initDictionary();
				$name = $this->translateSingleton($building, casus: Casus::Nominative) . ' ' . $id;
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
			$this->newDefault = new Construction(new Phrase('MACHEN Gebäude'), $this->context, $this->job);
			$this->initializeMarket($construction);
			$this->addConstructionExtensions($construction);
			$this->addConstructionEffects($construction);
		} else {
			if ($this->capability > 0) {
				if ($this->remainingSize > 0) {
					if ($construction) {
						$this->message(ConstructionResourcesMessage::class)->e($construction);
					} else {
						$this->message(ConstructionCreateMessage::class)->s($building);
					}
				} else {
					$this->message(ConstructionSizeMessage::class)->s($building);
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
			foreach ($this->unit->Region()->Estate() as $construction) {
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

	private function calculateRemainingSize(): void {
		/** @var Building $building */
		$building = $this->job->getObject();
		if (!($building instanceof Castle)) {
			$maxSize = $building->MaxSize();
			if ($maxSize !== Building::IS_UNLIMITED) {
				$this->remainingSize = max(0, $maxSize - $this->size);
			}
		}
	}

	private function prepareBuildingFromOutside(): ?ConstructionModel {
		$building = $this->job->getObject();
		if ($building::class === AnyBuilding::class) {
			$knowledge = $this->unit->Knowledge();
			$talent    = $building->getCraft()->Talent();
			$ability   = $knowledge[$talent];
			if ($ability->Count() > 0 && $this->phrase->count() >= 2) {
				$id     = $this->parseId(2);
				$estate = $this->unit->Region()->Estate();
				if ($estate->has($id)) {
					$construction = $estate[$id];
					$building     = $construction->Building();
					if (!($building instanceof AbstractVenue) && !in_array($building::class, self::FORBIDDEN)) {
						return $construction;
					}
				}
			}
		}
		return null;
	}

	private function replacePlaceholderJob(): void {
		$building = $this->job->getObject();
		if ($building instanceof AbstractVenue) {
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
					$demand    = $this->phrase->count() === 2 ? PHP_INT_MAX : $this->job->Count();
					$this->job = new Job($this->fromOutside->Building(), $demand);
					$this->fromOutside->Inhabitants()->add($this->unit);
				}
			}
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
		if ($construction?->Building() === $building) {
			return $construction;
		}
		if ($construction) {
			$construction->Inhabitants()->remove($this->unit);
			$this->message(LeaveConstructionMessage::class)->e($construction);
		}
		return null;
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
		$extensions      = $construction->Extensions();
		$building        = $construction->Building();
		$extensionsToAdd = self::EXTENSIONS[$building::class] ?? [];
		foreach ($extensionsToAdd as $class) {
			if (!$extensions->offsetExists($class)) {
				$extensions->add(new $class());
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

	private function monumentEffect(ConstructionModel $monument): DecayEffect {
		$effect = new DecayEffect(State::getInstance());
		/** @var DecayEffect $monumentEffect */
		$monumentEffect = Lemuria::Score()->find($effect->setConstruction($monument)->setInterval(DecayEffect::MONUMENT));
		return $monumentEffect ?? $effect;
	}

	private function signpostEffect(ConstructionModel $signpost): SignpostEffect {
		$effect = new SignpostEffect(State::getInstance());
		/** @var SignpostEffect $signpostEffect */
		$signpostEffect = Lemuria::Score()->find($effect->setConstruction($signpost));
		return $signpostEffect ?? $effect;
	}
}
