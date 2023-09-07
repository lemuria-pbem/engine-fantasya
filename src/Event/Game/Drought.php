<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Event\Game;

use Lemuria\Engine\Fantasya\Event\AbstractEvent;
use Lemuria\Engine\Fantasya\Factory\OptionsTrait;
use Lemuria\Engine\Fantasya\Message\Party\Event\DroughtMessage;
use Lemuria\Engine\Fantasya\Message\Region\Event\DroughtRegionMessage;
use Lemuria\Engine\Fantasya\Priority;
use Lemuria\Engine\Fantasya\State;
use Lemuria\Model\Fantasya\Commodity;
use Lemuria\Model\Fantasya\Commodity\Wood;
use Lemuria\Model\Fantasya\Factory\BuilderTrait;
use Lemuria\Model\Fantasya\Landscape\Forest;
use Lemuria\Model\Fantasya\Landscape\Highland;
use Lemuria\Model\Fantasya\Landscape\Mountain;
use Lemuria\Model\Fantasya\Party;
use Lemuria\Model\Fantasya\Quantity;
use Lemuria\Model\Fantasya\Region;

/**
 * A drought occurs in higher regions and parts of the forests wither.
 */
final class Drought extends AbstractEvent
{
	use BuilderTrait;
	use OptionsTrait;

	public final const RATE = 'rate';

	private const THRESHOLD = [Forest::class => 850, Highland::class => 100, Mountain::class => 50];

	private const MAXIMUM = [Forest::class => 1000, Highland::class => 400, Mountain::class => 100];

	private float $rate;

	private Commodity $wood;

	public function __construct(State $state) {
		parent::__construct($state, Priority::Before);
		$this->wood = self::createCommodity(Wood::class);
	}

	public function setOptions(array $options): Drought {
		$this->options = $options;
		return $this;
	}

	protected function initialize(): void {
		$this->rate = $this->getOption(self::RATE, 'float');
	}

	protected function run(): void {
		foreach (Party::all() as $party) {
			if (!$party->hasRetired()) {
				$this->message(DroughtMessage::class, $party);
			}
		}

		foreach (Region::all() as $region) {
			$landscape = $region->Landscape()::class;
			$threshold = self::THRESHOLD[$landscape] ?? null;
			if ($threshold) {
				$resources = $region->Resources();
				$forest    = $resources[$this->wood];
				if ($forest) {
					$trees   = $forest->Count();
					$maximum = self::MAXIMUM[$landscape];
					$excess  = max(0, $trees - $maximum);
					$trees  -= $excess;
					if ($trees > $threshold) {
						$wither = (int)round($this->rate * ($trees - $threshold)) + $excess;
						if ($wither > 0) {
							$deadTrees = new Quantity($this->wood, $wither);
							$resources->remove($deadTrees);
							$this->message(DroughtRegionMessage::class, $region)->i($deadTrees);
						}
					}
				}
			}
		}
	}
}
