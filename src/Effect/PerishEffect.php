<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Effect;

use function Lemuria\randElement;
use Lemuria\Engine\Fantasya\Command\Operate\Carcass;
use Lemuria\Engine\Fantasya\Factory\BuilderTrait as CarcassBuilderTrait;
use Lemuria\Engine\Fantasya\Factory\MessageTrait;
use Lemuria\Engine\Fantasya\Message\Region\PerishWashedAshoreMessage;
use Lemuria\Engine\Fantasya\Priority;
use Lemuria\Engine\Fantasya\State;
use Lemuria\Lemuria;
use Lemuria\Model\Fantasya\Factory\BuilderTrait;
use Lemuria\Model\Fantasya\Navigable;
use Lemuria\Model\Fantasya\Quantity;
use Lemuria\Model\Fantasya\Region;
use Lemuria\Model\Fantasya\Resources;
use Lemuria\Model\Fantasya\TrophySource;
use Lemuria\Model\Fantasya\Unicum;

final class PerishEffect extends AbstractUnitEffect
{
	use BuilderTrait;
	use CarcassBuilderTrait;
	use MessageTrait;

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
		$unit      = $this->Unit();
		$race      = $unit->Race();
		$inventory = new Resources();
		$inventory->fill($unit->Inventory());
		if ($race instanceof TrophySource) {
			$trophy = $race->Trophy();
			if ($trophy && isset(Carcass::WITH_TROPHY[$race::class])) {
				$inventory->add(new Quantity($trophy));
			}
		}
		return $this->createNamedCarcass($race, $inventory);
	}
}
