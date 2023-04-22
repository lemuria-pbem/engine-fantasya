<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Factory\Namer;

use Lemuria\Engine\Fantasya\Factory\GrammarTrait;
use Lemuria\Entity;
use Lemuria\Exception\NamerException;
use Lemuria\Factory\Namer;
use Lemuria\Identifiable;
use Lemuria\Model\Domain;
use Lemuria\Model\Fantasya\Construction;
use Lemuria\Model\Fantasya\Continent;
use Lemuria\Model\Fantasya\Party;
use Lemuria\Model\Fantasya\Region;
use Lemuria\Model\Fantasya\Market\Trade;
use Lemuria\Model\Fantasya\Unicum;
use Lemuria\Model\Fantasya\Unit;
use Lemuria\Model\Fantasya\Vessel;
use Lemuria\Singleton;

class DefaultNamer implements Namer
{
	use GrammarTrait;

	/**
	 * @throws NamerException
	 */
	public function name(Domain|Identifiable|Entity $entity): string {
		$domain = strtolower($entity instanceof Identifiable ? $entity->Catalog()->name : $entity->name);
		try {
			$name = $this->$domain($entity);
		} catch (\Throwable $e) {
			throw new NamerException($e->getMessage(), previous: $e);
		}
		if ($entity instanceof Entity) {
			$entity->setName($name);
		}
		return $name;
	}

	protected function construction(Construction $construction): string {
		return $this->translate($construction->Building(), $construction);
	}

	protected function continent(Continent $continent): string {
		return 'Kontinent ' . $continent->Id();
	}

	protected function location(Region $region): string {
		return $this->translate($region->Landscape(), $region);
	}

	protected function party(Party $party): string {
		return 'Partei ' . $party->Id();
	}

	protected function trade(Trade $trade): string {
		return ($trade->Trade() === Trade::OFFER ? 'Angebot ' : 'Gesuch ') . $trade->Id();
	}

	protected function unicum(Unicum $unicum): string {
		return $this->translate($unicum->Composition(), $unicum);
	}

	protected function unit(Unit $unit): string {
		return 'Einheit ' . $unit->Id();
	}

	protected function vessel(Vessel $vessel): string {
		return $this->translate($vessel->Ship(), $vessel);
	}

	protected function translate(Singleton $singleton, Identifiable $entity): string {
		return $this->translateSingleton($singleton) . ' ' . $entity->Id();
	}
}
