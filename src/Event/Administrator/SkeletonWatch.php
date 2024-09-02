<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Event\Administrator;

use Lemuria\Engine\Fantasya\Event\AbstractEvent;
use Lemuria\Engine\Fantasya\Factory\OptionsTrait;
use Lemuria\Engine\Fantasya\Priority;
use Lemuria\Engine\Fantasya\State;
use Lemuria\Lemuria;
use Lemuria\Model\Fantasya\Commodity\Monster\Skeleton;
use Lemuria\Model\Fantasya\Factory\BuilderTrait;
use Lemuria\Model\Fantasya\Region;

/**
 * This event let skeletons guard the region so they will attack.
 */
final class SkeletonWatch extends AbstractEvent
{
	use BuilderTrait;
	use OptionsTrait;

	public final const string REGION = 'region';

	private Region $region;

	public function __construct(State $state) {
		parent::__construct($state, Priority::Before);
	}

	public function setOptions(array $options): SkeletonWatch {
		$this->options = $options;
		return $this;
	}

	protected function initialize(): void {
		$this->region = Region::get($this->getIdOption(self::REGION));
	}

	protected function run(): void {
		$skeleton = self::createRace(Skeleton::class);
		$monsters = $this->state->getTurnOptions()->Finder()->Party()->findByRace($skeleton);
		foreach ($this->region->Residents() as $unit) {
			if ($unit->Party() === $monsters && $unit->Race() === $skeleton) {
				if (!$unit->IsGuarding()) {
					Lemuria::Log()->debug($unit . ' is now guarding.');
					$unit->setIsGuarding(true);
				}
			}
		}
	}
}
