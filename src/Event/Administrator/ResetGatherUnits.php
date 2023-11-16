<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Event\Administrator;

use Lemuria\Engine\Fantasya\Event\AbstractEvent;
use Lemuria\Engine\Fantasya\Factory\OptionsTrait;
use Lemuria\Engine\Fantasya\Priority;
use Lemuria\Engine\Fantasya\State;
use Lemuria\Id;
use Lemuria\Lemuria;
use Lemuria\Model\Fantasya\Party;

/**
 * This event sets the gather status of all units in a specific party.
 */
final class ResetGatherUnits extends AbstractEvent
{
	use OptionsTrait;

	public final const PARTY = 'party';

	public final const IS_LOOTING = 'isLooting';

	private Party $party;

	private bool $isLooting;

	public function __construct(State $state) {
		parent::__construct($state, Priority::Before);
	}

	public function setOptions(array $options): ResetGatherUnits {
		$this->options = $options;
		return $this;
	}

	protected function initialize(): void {
		$this->party     = Party::get(new Id($this->getOption(self::PARTY, 'int')));
		$this->isLooting = $this->getOption(self::IS_LOOTING, 'bool');
	}

	protected function run(): void {
		$this->party->Presettings()->setIsLooting($this->isLooting);
		$people = $this->party->People();
		foreach ($people as $unit) {
			$unit->setIsLooting($this->isLooting);
		}
		$set   = $this->isLooting ? 'set' : 'unset';
		$count = $people->count();
		Lemuria::Log()->debug('In party ' . $this->party . ' IsLooting has been ' . $set . ' for ' . $count . ' units.');
	}
}
