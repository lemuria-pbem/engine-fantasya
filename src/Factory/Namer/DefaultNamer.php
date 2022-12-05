<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Factory\Namer;

use function Lemuria\getClass;
use Lemuria\Factory\Namer;
use Lemuria\Identifiable;
use Lemuria\Model\Domain;
use Lemuria\Model\Dictionary;
use Lemuria\Model\Fantasya\Construction;
use Lemuria\Model\Fantasya\Continent;
use Lemuria\Model\Fantasya\Party;
use Lemuria\Model\Fantasya\Region;
use Lemuria\Model\Fantasya\Market\Trade;
use Lemuria\Model\Fantasya\Unicum;
use Lemuria\Model\Fantasya\Unit;
use Lemuria\Model\Fantasya\Vessel;

class DefaultNamer implements Namer
{
	protected readonly Dictionary $dictionary;

	public function __construct() {
		$this->dictionary = new Dictionary();
	}

	public function name(Domain|Identifiable $entity): string {
		$domain = strtolower($entity instanceof Identifiable ? $entity->Catalog()->name : $entity->name);
		return $this->$domain($entity);
	}

	protected function construction(Construction $construction): string {
		$building = getClass($construction->Building());
		return $this->dictionary->get('building.' . $building) . ' ' . $construction->Id();
	}

	protected function continent(Continent $continent): string {
		return 'Kontinent ' . $continent->Id();
	}

	protected function location(Region $region): string {
		$landscape = getClass($region->Landscape());
		return $this->dictionary->get('landscape.' . $landscape) . ' ' . $region->Id();
	}

	protected function party(Party $party): string {
		return 'Partei ' . $party->Id();
	}

	protected function trade(Trade $trade): string {
		return ($trade->Trade() === Trade::OFFER ? 'Angebot ' : 'Gesuch ') . $trade->Id();
	}

	protected function unicum(Unicum $unicum): string {
		$composition = getClass($unicum->Composition());
		return $this->dictionary->get('composition.' . $composition) . ' ' . $unicum->Id();
	}

	protected function unit(Unit $unit): string {
		return 'Einheit ' . $unit->Id();
	}

	protected function vessel(Vessel $vessel): string {
		$ship = getClass($vessel->Ship());
		return $this->dictionary->get('ship.' . $ship) . ' ' . $vessel->Id();
	}
}
