<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Command\Operate;

use Lemuria\Id;
use Lemuria\Model\Exception\NotRegisteredException;
use Lemuria\Model\Fantasya\Chronicle;
use Lemuria\Model\Fantasya\Composition\HerbAlmanac as Almanac;
use Lemuria\Model\Fantasya\HerbalBook;
use Lemuria\Model\Fantasya\Region;

final class HerbAlmanac extends AbstractOperate
{
	private const DISINTEGRATE = 4;

	use BurnTrait;

	public function apply(): void {
		$herbalBook = $this->getAlmanac()->HerbalBook();
		$unit       = $this->operator->Unit();
		$chronicle  = $unit->Party()->Chronicle();
		$id         = $this->operator->Phrase()->getLine($this->operator->ArgumentIndex());
		if ($id) {
			try {
				$id = Id::fromId($id);
				$this->addHerbage(Region::get($id), $chronicle);
			} catch (NotRegisteredException) {
				//TODO unknown
				return;
			}
		} else {
			if ($herbalBook->count() <= 0) {
				//TODO empty
				return;
			}
			foreach ($herbalBook as $region /* @var Region $region */) {
				$this->addHerbage($region, $chronicle);
			}
		}
	}

	public function write(): void {
		$herbalBook = $this->operator->Unit()->Party()->HerbalBook();
		$almanac    = $this->getAlmanac()->HerbalBook();
		$id         = $this->operator->Phrase()->getLine($this->operator->ArgumentIndex());
		if ($id) {
			try {
				$id = Id::fromId($id);
				$this->writeHerbage($almanac, Region::get($id), $herbalBook);
			} catch (NotRegisteredException) {
				//TODO unknown
				return;
			}
		} else {
			if ($herbalBook->count() <= 0) {
				//TODO empty
				return;
			}
			foreach ($herbalBook as $region /* @var Region $region */) {
				$this->writeHerbage($almanac, $region, $herbalBook);
			}
		}
	}

	protected function addLooseEffect(): void {
		$this->addDisintegrateEffectForRegion(self::DISINTEGRATE);
	}

	private function getAlmanac(): Almanac {
		/** @var Almanac $almanac */
		$almanac = $this->operator->Unicum()->Composition();
		return $almanac;
	}

	private function addHerbage(Region $region, Chronicle $chronicle): void {
		//TODO
	}

	private function writeHerbage(HerbalBook $almanac, Region $region, HerbalBook $herbalBook): void {
		//TODO
	}
}
