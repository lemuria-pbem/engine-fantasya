<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Command\Operate;

use Lemuria\Engine\Fantasya\Message\Unit\Operate\HerbageApplyAllMessage;
use Lemuria\Engine\Fantasya\Message\Unit\Operate\HerbageApplyEmptyMessage;
use Lemuria\Engine\Fantasya\Message\Unit\Operate\HerbageApplyMessage;
use Lemuria\Engine\Fantasya\Message\Unit\Operate\HerbageApplySkipMessage;
use Lemuria\Engine\Fantasya\Message\Unit\Operate\HerbageApplyUnknownMessage;
use Lemuria\Engine\Fantasya\Message\Unit\Operate\HerbageWriteAllMessage;
use Lemuria\Engine\Fantasya\Message\Unit\Operate\HerbageWriteEmptyMessage;
use Lemuria\Engine\Fantasya\Message\Unit\Operate\HerbageWriteRegionMessage;
use Lemuria\Engine\Fantasya\Message\Unit\Operate\HerbageWriteUnknownMessage;
use Lemuria\Id;
use Lemuria\Model\Exception\NotRegisteredException;
use Lemuria\Model\Fantasya\Chronicle;
use Lemuria\Model\Fantasya\Composition\HerbAlmanac as Almanac;
use Lemuria\Model\Fantasya\Herbage;
use Lemuria\Model\Fantasya\HerbalBook;
use Lemuria\Model\Fantasya\Region;

final class HerbAlmanac extends AbstractOperate
{
	private const DISINTEGRATE = 4;

	use BurnTrait;

	public function apply(): void {
		$almanac    = $this->getAlmanac()->HerbalBook();
		$unicum     = $this->operator->Unicum();
		$unit       = $this->operator->Unit();
		$herbalBook = $unit->Party()->HerbalBook();
		$chronicle  = $unit->Party()->Chronicle();
		$id         = $this->operator->Phrase()->getLine($this->operator->ArgumentIndex());
		if ($id) {
			try {
				$id     = Id::fromId($id);
				$region = Region::get($id);
				$this->addHerbage($almanac, $region, $herbalBook, $chronicle);
				$this->message(HerbageApplyMessage::class,$this->unit)->e($unicum)->e($region, HerbageApplyMessage::REGION);
			} catch (NotRegisteredException) {
				$this->message(HerbageApplyUnknownMessage::class, $unit)->p((string)$id);
				return;
			}
		} else {
			if ($herbalBook->count() <= 0) {
				$this->message(HerbageApplyEmptyMessage::class, $this->unit)->e($unicum);
				return;
			}
			$this->message(HerbageApplyAllMessage::class, $this->unit)->e($unicum);
			foreach ($herbalBook as $region /* @var Region $region */) {
				$this->addHerbage($almanac, $region, $herbalBook, $chronicle);
			}
		}
	}

	public function write(): void {
		$herbalBook = $this->operator->Unit()->Party()->HerbalBook();
		$almanac    = $this->getAlmanac()->HerbalBook();
		$id         = $this->operator->Phrase()->getLine($this->operator->ArgumentIndex());
		if ($id) {
			try {
				$id     = Id::fromId($id);
				$region = Region::get($id);
				$this->writeHerbage($almanac, $region, $herbalBook);
				$unicum = $this->operator->Unicum();
				$this->message(HerbageWriteRegionMessage::class, $this->unit)->e($unicum)->e($region, HerbageWriteRegionMessage::REGION);
			} catch (NotRegisteredException) {
				$this->message(HerbageWriteUnknownMessage::class, $this->unit)->p((string)$id);
				return;
			}
		} else {
			if ($herbalBook->count() <= 0) {
				$this->message(HerbageWriteEmptyMessage::class, $this->unit);
				return;
			}
			foreach ($herbalBook as $region /* @var Region $region */) {
				$this->writeHerbage($almanac, $region, $herbalBook);
			}
			$this->message(HerbageWriteAllMessage::class, $this->unit)->e($this->operator->Unicum());
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

	private function addHerbage(HerbalBook $almanac, Region $region, HerbalBook $herbalBook, Chronicle $chronicle): void {
		if ($chronicle->has($region->Id())) {
			$herbage = $almanac->getHerbage($region);
			$visit   = $almanac->getVisit($region);
			$round   = $visit->Round();
			if ($herbalBook->getVisit($region)->Round() < $round) {
				$herb       = $herbage->Herb();
				$occurrence = $herbage->Occurrence();
				$herbage    = new Herbage($herb);
				$herbalBook->record($region, $herbage->setOccurrence($occurrence), $round);
			} else {
				$unicum = $this->operator->Unicum();
				$this->message(HerbageApplySkipMessage::class, $this->unit)->e($unicum)->e($region, HerbageApplyMessage::REGION);
			}
		} else {
			$this->message(HerbageApplyUnknownMessage::class, $this->unit)->p((string)$region->Id());
		}
	}

	private function writeHerbage(HerbalBook $almanac, Region $region, HerbalBook $herbalBook): void {
		$herbage    = $herbalBook->getHerbage($region);
		$visit      = $herbalBook->getVisit($region);
		$herb       = $herbage->Herb();
		$occurrence = $herbage->Occurrence();
		$herbage    = new Herbage($herb);
		$almanac->record($region, $herbage->setOccurrence($occurrence), $visit->Round());
	}
}
