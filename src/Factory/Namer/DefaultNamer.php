<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Factory\Namer;

use function Lemuria\getClass;
use Lemuria\Exception\NamerException;
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
	protected static ?Dictionary $dictionary = null;

	/**
	 * @throws NamerException
	 */
	public function name(Domain|Identifiable $entity): string {
		$domain = strtolower($entity instanceof Identifiable ? $entity->Catalog()->name : $entity->name);
		try {
			return $this->$domain($entity);
		} catch (\Throwable $e) {
			throw new NamerException($e->getMessage(), previous: $e);
		}
	}

	protected static function dictionary(): Dictionary {
		if (!self::$dictionary) {
			self::$dictionary = new Dictionary();
		}
		return self::$dictionary;
	}
	protected static function translate(string $key, Identifiable $entity): string {
		return self::dictionary()->get($key) . ' ' . $entity->Id();
	}

	protected function construction(Construction $construction): string {
		$building = getClass($construction->Building());
		return self::translate('building.' . $building, $construction);
	}

	protected function continent(Continent $continent): string {
		return 'Kontinent ' . $continent->Id();
	}

	protected function location(Region $region): string {
		$landscape = getClass($region->Landscape());
		return self::translate('landscape.' . $landscape, $region);
	}

	protected function party(Party $party): string {
		return 'Partei ' . $party->Id();
	}

	protected function trade(Trade $trade): string {
		return ($trade->Trade() === Trade::OFFER ? 'Angebot ' : 'Gesuch ') . $trade->Id();
	}

	protected function unicum(Unicum $unicum): string {
		$composition = getClass($unicum->Composition());
		return self::translate('composition.' . $composition, $unicum);
	}

	protected function unit(Unit $unit): string {
		return 'Einheit ' . $unit->Id();
	}

	protected function vessel(Vessel $vessel): string {
		$ship = getClass($vessel->Ship());
		return self::translate('ship.' . $ship, $vessel);
	}
}
