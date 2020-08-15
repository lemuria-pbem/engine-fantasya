<?php
declare (strict_types = 1);
namespace Lemuria\Engine\Lemuria\Command;

use Lemuria\Engine\Lemuria\Exception\CommandException;
use Lemuria\Engine\Lemuria\Message\Construction\DescribeCastleMessage;
use Lemuria\Engine\Lemuria\Message\Construction\DescribeConstructionMessage;
use Lemuria\Engine\Lemuria\Message\Construction\DescribeOwnerMessage;
use Lemuria\Engine\Lemuria\Message\Region\DescribeRegionMessage;
use Lemuria\Engine\Lemuria\Message\Unit\DescribeUnitMessage;
use Lemuria\Engine\Lemuria\Message\Unit\DescribeNotInConstructionMessage;
use Lemuria\Engine\Lemuria\Message\Unit\DescribeNotInVesselMessage;
use Lemuria\Engine\Lemuria\Message\Vessel\DescribeCaptainMessage;
use Lemuria\Engine\Lemuria\Message\Vessel\DescribeVesselMessage;
use Lemuria\Model\Lemuria\Building\Castle;
use Lemuria\Model\Lemuria\Construction;

/**
 * The Describe command is used to set the description of a unit or the construction, region or vessel it controls.
 *
 * - BESCHREIBE [Einheit] <Name>
 * - BESCHREIBE Burg|Gebäude <Name>
 * - BESCHREIBE Region <Name>
 * - BESCHREIBE Schiff <Name>
 */
final class Describe extends UnitCommand {

	/**
	 * The command implementation.
	 */
	protected function run(): void {
		$n = $this->phrase->count();
		if ($n <= 0) {
			throw new CommandException('No description given.');
		}
		if ($n === 1) {
			$type        = 'Einheit';
			$description = $this->phrase->getParameter();
		} else {
			$type        = $this->phrase->getParameter(1);
			$description = $this->trimDescription($this->phrase->getLine(2));
		}

		switch (strtolower($type)) {
			case 'einheit' :
				$this->describeUnit($description);
				break;
			case 'burg' :
			case 'gebäude' :
			case 'gebaeude':
				$this->describeConstruction($description);
				break;
			case 'region' :
				$this->describeRegion($description);
				break;
			case 'schiff' :
				$this->describeVessel($description);
				break;
			default :
				$this->describeUnit($this->trimDescription($this->phrase->getLine()));
		}
	}

	/**
	 * Set description of unit.
	 *
	 * @param string $description
	 */
	private function describeUnit(string $description): void {
		$this->unit->setDescription($description);
		$this->message(DescribeUnitMessage::class);
	}

	/**
	 * Set description of construction the unit controls.
	 *
	 * @param string $description
	 */
	private function describeConstruction(string $description): void {
		$construction = $this->unit->Construction();
		if ($construction) {
			$owner = $construction->Inhabitants()->Owner();
			if ($owner && $owner === $this->unit) {
				$construction->setDescription($description);
				$this->message(DescribeConstructionMessage::class)->e($construction);
				return;
			}
			$this->message(DescribeOwnerMessage::class)->e($construction)->e($this->unit, DescribeOwnerMessage::OWNER);
			return;
		}
		$this->message(DescribeNotInConstructionMessage::class);
	}

	/**
	 * Set description of region the unit controls.
	 *
	 * @param string $description
	 */
	private function describeRegion(string $description): void {
		$region = $this->unit->Region();
		$home   = $this->unit->Construction();
		if ($home) {
			$castle = null; /* @var Construction $castle */
			foreach ($region->Estate() as $construction /* @var Construction $construction */) {
				if ($construction->Building() instanceof Castle) {
					if (!$castle || $construction->Size() >= $castle->Size()) {
						$castle = $construction;
					}
				}
			}
			if ($castle === $home && $home->Inhabitants()->Owner() === $this->unit) {
				$region->setDescription($description);
				$this->message(DescribeRegionMessage::class)->e($region);
				return;
			}
		}
		$this->message(DescribeCastleMessage::class)->e($region)->e($this->unit, DescribeCastleMessage::OWNER);
	}

	/**
	 * Set description of vessel the unit controls.
	 *
	 * @param string $description
	 */
	private function describeVessel(string $description): void {
		$vessel = $this->unit->Vessel();
		if ($vessel) {
			$captain = $vessel->Passengers()->Owner();
			if ($captain && $captain === $this->unit) {
				$vessel->setDescription($description);
				$this->message(DescribeVesselMessage::class)->e($vessel);
				return;
			}
			$this->message(DescribeCaptainMessage::class)->e($vessel)->e($this->unit, DescribeCaptainMessage::CAPTAIN);
			return;
		}
		$this->message(DescribeNotInVesselMessage::class);
	}

	/**
	 * Trim special characters from description.
	 *
	 * @param string $description
	 * @return string
	 */
	private function trimDescription(string $description): string {
		return trim($description, "\"'`'^°§$%&/()={[]}\\+*~#<>|,-;:_ ");
	}
}
