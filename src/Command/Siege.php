<?php
declare (strict_types = 1);
namespace Lemuria\Engine\Fantasya\Command;

use Lemuria\Engine\Fantasya\Effect\SiegeEffect;
use Lemuria\Engine\Fantasya\Exception\InvalidCommandException;
use Lemuria\Engine\Fantasya\Message\Unit\SiegeDamageMessage;
use Lemuria\Engine\Fantasya\Message\Unit\SiegeDestroyMessage;
use Lemuria\Engine\Fantasya\Message\Unit\SiegeLeaveMessage;
use Lemuria\Engine\Fantasya\Message\Unit\SiegeMessage;
use Lemuria\Engine\Fantasya\Message\Unit\SiegeNotFightingMessage;
use Lemuria\Engine\Fantasya\Message\Unit\SiegeNotFoundMessage;
use Lemuria\Engine\Fantasya\Message\Unit\SiegeNotGuardingMessage;
use Lemuria\Engine\Fantasya\Message\Unit\SiegeNotOurMessage;
use Lemuria\Engine\Fantasya\Message\Unit\SiegeNotOurselvesMessage;
use Lemuria\Engine\Fantasya\Message\Unit\SiegeUnguardMessage;
use Lemuria\Engine\Fantasya\State;
use Lemuria\Id;
use Lemuria\Lemuria;
use Lemuria\Model\Fantasya\Combat\BattleRow;
use Lemuria\Model\Fantasya\Commodity\Weapon\Catapult;
use Lemuria\Model\Fantasya\Construction;
use Lemuria\Model\Fantasya\Factory\BuilderTrait;
use Lemuria\Model\Fantasya\Talent\Catapulting;
use Lemuria\Model\Fantasya\Unit;

/**
 * Siege constructions.
 *
 * - BELAGERN <construction>
 */
final class Siege extends UnitCommand
{
	use BuilderTrait;

	private ?Construction $construction = null;

	private Catapult $catapult;

	protected function initialize(): void {
		parent::initialize();
		if ($this->unit->BattleRow() <= BattleRow::Bystander) {
			$this->message(SiegeNotFightingMessage::class);
			return;
		}
		if (!$this->unit->IsGuarding()) {
			$this->message(SiegeNotGuardingMessage::class);
			return;
		}
		if ($this->phrase->count() !== 1) {
			throw new InvalidCommandException($this);
		}

		$id     = $this->parseId()->Id();
		$region = $this->unit->Region();
		foreach ($region->Estate() as $construction /* @var Construction $construction */) {
			if ($construction->Id()->Id() === $id) {
				$this->construction = $construction;
				break;
			}
		}
		if (!$this->construction) {
			$this->message(SiegeNotFoundMessage::class)->p($id);
			return;
		}
		$construction = $this->unit->Construction();
		if ($this->construction === $construction) {
			$this->message(SiegeNotOurselvesMessage::class);
			return;
		}
		if ($this->construction->Inhabitants()->Owner()?->Party() === $this->unit->Party()) {
			$this->message(SiegeNotOurMessage::class);
			return;
		}

		if ($construction) {
			$construction->Inhabitants()->remove($this->unit);
			$this->message(SiegeLeaveMessage::class);
		}
		$this->commitCommand($this);
	}

	protected function run(): void {
		if ($this->context->getTurnOptions()->IsSimulation()) {
			return;
		}
		$siege = $this->context->getSiege($this->construction);
		if ($siege->IsSieged()) {
			return;
		}

		/** @var Catapult $catapult */
		$catapult       = self::createCommodity(Catapult::class);
		$this->catapult = $catapult;

		$siege->siege();
		$besiegers = $siege->Besiegers();
		if ($besiegers->count()) {
			$effect = $this->createEffect();
		}
		foreach ($this->construction->Inhabitants() as $unit /* @var Unit $unit */) {
			if ($unit->IsGuarding()) {
				$unit->setIsGuarding(false);
				$this->message(SiegeUnguardMessage::class, $unit);
			}
		}
		foreach ($besiegers as $unit /* @var Unit $unit */) {
			/** @noinspection PhpUndefinedVariableInspection */
			$effect->renew($unit);
			$this->message(SiegeMessage::class, $unit)->e($this->construction);
			$this->useCatapults($unit);
		}
		if ($this->construction->Size() <= 0) {
			$this->destroyConstruction();
		}
	}

	protected function commitCommand(UnitCommand $command): void {
		if ($this->construction) {
			$this->context->getSiege($this->construction)->Besiegers()->add($this->unit);
			parent::commitCommand($command);
		}
	}

	private function useCatapults(Unit $unit): void {
		$level     = $this->context->getCalculus($unit)->knowledge(Catapulting::class)->Level();
		$catapults = $unit->Inventory()[$this->catapult]->Count();
		$size      = $this->construction->Size();
		if ($level > 0 && $catapults > 0) {
			$damage = $catapults * $level;
			if ($size > 0) {
				$size = max(0, $size - $damage);
				$this->construction->setSize($size);
			}
			$this->message(SiegeDamageMessage::class, $unit)->e($this->construction)->p($damage);
		}
	}

	private function destroyConstruction(): void {
		$this->message(SiegeDestroyMessage::class, $this->unit->Region())->e($this->construction);
		Lemuria::Catalog()->reassign($this->construction);
		$this->construction->Inhabitants()->clear();
		$this->construction->Region()->Estate()->remove($this->construction);
		Lemuria::Catalog()->remove($this->construction);
	}

	private function createEffect(): SiegeEffect {
		$effect   = new SiegeEffect(State::getInstance());
		$existing = Lemuria::Score()->find($effect->setConstruction($this->construction));
		if ($existing instanceof SiegeEffect) {
			return $existing;
		}
		Lemuria::Score()->add($effect);
		return $effect;
	}
}
