<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Effect;

use function Lemuria\randElement;
use Lemuria\Engine\Fantasya\Factory\GrammarTrait;
use Lemuria\Engine\Fantasya\Factory\MessageTrait;
use Lemuria\Engine\Fantasya\Message\Casus;
use Lemuria\Engine\Fantasya\Message\Region\PerishWashedAshoreMessage;
use Lemuria\Engine\Fantasya\Priority;
use Lemuria\Engine\Fantasya\State;
use Lemuria\Lemuria;
use Lemuria\Model\Domain;
use Lemuria\Model\Fantasya\Commodity\Griffin;
use Lemuria\Model\Fantasya\Commodity\Monster\Bear;
use Lemuria\Model\Fantasya\Composition\Carcass;
use Lemuria\Model\Fantasya\Factory\BuilderTrait;
use Lemuria\Model\Fantasya\Monster;
use Lemuria\Model\Fantasya\Navigable;
use Lemuria\Model\Fantasya\Quantity;
use Lemuria\Model\Fantasya\Region;
use Lemuria\Model\Fantasya\Resources;
use Lemuria\Model\Fantasya\Unicum;

final class PerishEffect extends AbstractUnitEffect
{
	use BuilderTrait;
	use GrammarTrait;
	use MessageTrait;

	private const TROPHY = [Bear::class => true, Griffin::class => true];

	public function __construct(State $state) {
		parent::__construct($state, Priority::After);
	}

	protected function run(): void {
		Lemuria::Score()->remove($this);
		$region    = $this->Unit()->Region();
		$landscape = $region->Landscape();
		if ($landscape instanceof Navigable) {
			$shore = $this->lookForShore($region);
			if ($shore) {
				$shore->Treasury()->add($this->createCarcass());
				$this->message(PerishWashedAshoreMessage::class, $shore);
				Lemuria::Log()->debug('Unit ' . $this->Unit() . ' perishes ashore ' . $shore . '.');
			} else {
				Lemuria::Log()->debug('Unit ' . $this->Unit() . ' perishes in ' . $region . ' ' . $region->Id() . '.');
			}
		} else {
			$region->Treasury()->add($this->createCarcass());
			Lemuria::Log()->debug('Unit ' . $this->Unit() . ' perishes in ' . $region . ' and leaves a carcass behind.');
		}
	}

	private function lookForShore(Region $region): ?Region {
		$candidates = [];
		foreach (Lemuria::World()->getNeighbours($region) as $region) {
			/** @var Region $region */
			if (!$region->Landscape() instanceof Navigable) {
				$candidates[] = $region;
			}
		}
		return empty($candidates) ? null : randElement($candidates);
	}

	private function createCarcass(): Unicum {
		$unit = $this->Unit();
		$race = $unit->Race();

		/** @var Carcass $carcass */
		$carcass = self::createComposition(Carcass::class);
		$carcass->setCreature($race);
		$inventory = new Resources();
		$carcass->setInventory($inventory);
		$inventory->fill($unit->Inventory());
		if ($race instanceof Monster) {
			$trophy = $race->Trophy();
			if ($trophy && isset(self::TROPHY[$trophy::class])) {
				$inventory->add(new Quantity($trophy));
			}
		}

		$name   = $this->translateSingleton($carcass, casus: Casus::Nominative) . ' '
			    . $this->combineGrammar($race, 'ein', Casus::Genitive);
		$unicum = new Unicum();
		$unicum->setId(Lemuria::Catalog()->nextId(Domain::Unicum));
		return $unicum->setComposition($carcass)->setName($name);
	}
}
