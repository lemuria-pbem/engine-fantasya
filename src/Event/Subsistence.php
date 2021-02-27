<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Lemuria\Event;

use JetBrains\PhpStorm\Pure;

use Lemuria\Engine\Lemuria\Action;
use Lemuria\Engine\Lemuria\Factory\Workplaces;
use Lemuria\Engine\Lemuria\Message\Region\SubsistenceMessage;
use Lemuria\Engine\Lemuria\State;
use Lemuria\Lemuria;
use Lemuria\Model\Catalog;
use Lemuria\Model\Lemuria\Building\Castle;
use Lemuria\Model\Lemuria\Commodity;
use Lemuria\Model\Lemuria\Commodity\Camel;
use Lemuria\Model\Lemuria\Commodity\Elephant;
use Lemuria\Model\Lemuria\Commodity\Horse;
use Lemuria\Model\Lemuria\Commodity\Peasant;
use Lemuria\Model\Lemuria\Commodity\Silver;
use Lemuria\Model\Lemuria\Commodity\Tree;
use Lemuria\Model\Lemuria\Factory\BuilderTrait;
use Lemuria\Model\Lemuria\Quantity;
use Lemuria\Model\Lemuria\Region;

/**
 * Peasants work for their living and increase their silver reserve.
 */
final class Subsistence extends AbstractEvent
{
	public const SILVER = 10;

	public const WAGE = 11;

	use BuilderTrait;

	private Workplaces $workplaces;

	private Commodity $peasant;

	private Commodity $silver;

	#[Pure] public function __construct(State $state) {
		parent::__construct($state, Action::MIDDLE);
		$this->workplaces = new Workplaces();
		$this->peasant    = self::createCommodity(Peasant::class);
		$this->silver     = self::createCommodity(Silver::class);
	}

	protected function run(): void {
		foreach (Lemuria::Catalog()->getAll(Catalog::LOCATIONS) as $region /* @var Region $region */) {
			$resources = $region->Resources();
			$peasants  = $resources[$this->peasant]->Count();
			if ($peasants > 0) {
				$government = $this->context->getIntelligence($region)->getGovernment();
				/** @var Castle $castle */
				$castle = $government?->Building();
				$wage   = $castle?->Wage() ?? self::WAGE;

				$trees     = $resources[self::createCommodity(Tree::class)]->Count();
				$horses    = $resources[self::createCommodity(Horse::class)]->Count();
				$camels    = $resources[self::createCommodity(Camel::class)]->Count();
				$elephants = $resources[self::createCommodity(Elephant::class)]->Count();
				$landscape = $region->Landscape();
				$used      = $this->workplaces->getUsed($horses, $camels, $elephants, $trees);
				$available = max(0, $landscape->Workplaces() - $used);

				$workers   = min($peasants, $available);
				$earnings  = $workers * $wage;
				$working   = new Quantity($this->peasant, $workers);
				$silver    = new Quantity($this->silver, $earnings);
				$resources->add($silver);
				$this->message(SubsistenceMessage::class, $region)->i($working)->i($silver, SubsistenceMessage::SILVER)->p($wage);
			}
		}
	}
}
