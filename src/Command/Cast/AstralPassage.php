<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Command\Cast;

use Lemuria\Engine\Fantasya\Factory\MessageTrait;
use Lemuria\Engine\Fantasya\Message\Unit\Cast\AstralPassageAlreadyMessage;
use Lemuria\Engine\Fantasya\Message\Unit\Cast\AstralPassageConstructionMessage;
use Lemuria\Engine\Fantasya\Message\Unit\Cast\AstralPassageNoConstructionMessage;
use Lemuria\Engine\Fantasya\Message\Unit\Cast\AstralPassageNoRegionMessage;
use Lemuria\Engine\Fantasya\Message\Unit\Cast\AstralPassageNoVesselMessage;
use Lemuria\Engine\Fantasya\Message\Unit\Cast\AstralPassageRegionMessage;
use Lemuria\Engine\Fantasya\Message\Unit\Cast\AstralPassageVesselMessage;
use Lemuria\Engine\Fantasya\Travel\MoveTrait;
use Lemuria\Exception\LemuriaException;
use Lemuria\Model\Domain;
use Lemuria\Model\Fantasya\Construction;
use Lemuria\Model\Fantasya\Factory\BuilderTrait;
use Lemuria\Model\Fantasya\Region;
use Lemuria\Model\Fantasya\Vessel;

final class AstralPassage extends AbstractCast
{
	use BuilderTrait;
	use MessageTrait;
	use MoveTrait;

	public function getReassignmentDomain(): ?Domain {
		$region       = $this->cast->Region();
		$construction = $this->cast->Construction();
		$vessel       = $this->cast->Vessel();
		if ($construction && !$region && !$vessel) {
			return Domain::Construction;
		}
		if ($vessel && !$region && !$construction) {
			return Domain::Vessel;
		}
		return null;
	}

	public function cast(): void {
		$region       = $this->cast->Region();
		$construction = $this->cast->Construction();
		$vessel       = $this->cast->Vessel();
		if ($region && !$construction && !$vessel) {
			$this->teleportToRegion($region);
		} elseif ($construction && !$region && !$vessel) {
			$this->teleportToConstruction($construction);
		} elseif ($vessel && !$region && !$construction) {
			$this->teleportToVessel($vessel);
		} else {
			throw new LemuriaException('Multiple domain targets are not allowed.');
		}
	}

	private function teleportToRegion(Region $target): void {
		$unit   = $this->cast->Unit();
		$region = $unit->Region();
		if ($region === $target) {
			$this->message(AstralPassageAlreadyMessage::class, $unit);
			return;
		}

		$party = $unit->Party();
		if ($party->Chronicle()->has($target->Id())) {
			$this->clearUnitStatus($unit);
			$this->clearConstructionOwner($unit);
			$this->clearVesselCaptain($unit);
			$unit->Aura()->consume($this->cast->Aura());
			$region->Residents()->remove($unit);
			$target->Residents()->add($unit);
			$party->Chronicle()->add($target);
			$this->message(AstralPassageRegionMessage::class, $unit)->e($target);
		} else {
			$this->message(AstralPassageNoRegionMessage::class, $unit)->e($target);
		}
	}

	private function teleportToConstruction(Construction $target): void {
		$unit         = $this->cast->Unit();
		$construction = $unit->Construction();
		if ($target === $construction) {
			$this->message(AstralPassageAlreadyMessage::class, $unit);
			return;
		}
		$region = $unit->Region();
		if ($target->Region() !== $region) {
			$this->message(AstralPassageNoConstructionMessage::class, $unit)->e($target);
			return;
		}

		$this->clearConstructionOwner($unit);
		$this->clearVesselCaptain($unit);
		$unit->Aura()->consume($this->cast->Aura());
		$target->Inhabitants()->add($unit);
		$this->message(AstralPassageConstructionMessage::class, $unit)->e($target);
	}

	private function teleportToVessel(Vessel $target): void {
		$unit   = $this->cast->Unit();
		$vessel = $unit->Vessel();
		if ($target === $vessel) {
			$this->message(AstralPassageAlreadyMessage::class, $unit);
			return;
		}
		$region = $unit->Region();
		if ($target->Region() !== $region) {
			$this->message(AstralPassageNoVesselMessage::class, $unit)->e($target);
			return;
		}

		$this->clearConstructionOwner($unit);
		$this->clearVesselCaptain($unit);
		$unit->Aura()->consume($this->cast->Aura());
		$target->Passengers()->add($unit);
		$this->message(AstralPassageVesselMessage::class, $unit)->e($target);
	}
}
