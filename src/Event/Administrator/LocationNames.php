<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Event\Administrator;

use Lemuria\Engine\Fantasya\Event\AbstractEvent;
use Lemuria\Engine\Fantasya\Factory\GrammarTrait;
use Lemuria\Engine\Fantasya\Priority;
use Lemuria\Engine\Fantasya\State;
use Lemuria\Exception\NamerException;
use Lemuria\Factory\Namer;
use Lemuria\Lemuria;
use Lemuria\Model\Fantasya\Region;

/**
 * This event is a one-time fix for regions that have no individual name.
 */
final class LocationNames extends AbstractEvent
{
	use GrammarTrait;

	private Namer $namer;

	public function __construct(State $state) {
		parent::__construct($state, Priority::After);
		$this->namer = Lemuria::Namer();
	}

	protected function run(): void {
		foreach (Region::all() as $region) {
			$landscape = $this->translateSingleton($region->Landscape());
			if ($region->Name() !== $landscape . ' ' . $region->Id()) {
				Lemuria::Log()->debug('Region ' . $region . ' already has a good name.');
				continue;
			}
			$oldName = $region->Name();
			try {
				$name = $this->namer->name($region);
				$region->setName($name);
				Lemuria::Log()->debug($oldName . ' is called ' . $name . ' now.');
			} catch (NamerException $e) {
				Lemuria::Log()->critical('Region ' . $region . ' could not be named.', ['exception' => $e]);
			}
		}
	}
}
