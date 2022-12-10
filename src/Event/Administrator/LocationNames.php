<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Event\Administrator;

use function Lemuria\getClass;
use Lemuria\Engine\Fantasya\Event\AbstractEvent;
use Lemuria\Engine\Fantasya\Priority;
use Lemuria\Engine\Fantasya\State;
use Lemuria\Factory\Namer;
use Lemuria\Lemuria;
use Lemuria\Model\Dictionary;
use Lemuria\Model\Domain;
use Lemuria\Model\Fantasya\Region;

/**
 * This event searches for overcrowded constructions.
 */
final class LocationNames extends AbstractEvent
{
	private Namer $namer;

	private Dictionary $dictionary;

	public function __construct(State $state) {
		parent::__construct($state, Priority::After);
		$this->namer      = Lemuria::Namer();
		$this->dictionary = new Dictionary();
	}

	protected function run(): void {
		foreach (Lemuria::Catalog()->getAll(Domain::Location) as $region /* @var Region $region */) {
			$landscape = $this->dictionary->get('landscape.' . getClass($region->Landscape()));
			if ($region->Name() !== $landscape . ' ' . $region->Id()) {
				Lemuria::Log()->debug('Region ' . $region . ' already has a good name.');
				continue;
			}
			$name = $this->namer->name($region);
			$region->setName($name);
			Lemuria::Log()->debug($region . ' is called ' . $name . ' now.');
		}
	}
}
