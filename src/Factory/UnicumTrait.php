<?php
declare (strict_types = 1);
namespace Lemuria\Engine\Fantasya\Factory;

use Lemuria\Engine\Fantasya\Command\Operate\AbstractOperate;
use Lemuria\Engine\Fantasya\Exception\InvalidCommandException;
use Lemuria\Engine\Fantasya\Message\Unit\OperatePracticeMessage;
use Lemuria\Engine\Fantasya\Message\Unit\TakeBoughtMessage;
use Lemuria\Engine\Fantasya\Message\Unit\TakeNotEnoughMessage;
use Lemuria\Engine\Fantasya\Message\Unit\TakeOfferMessage;
use Lemuria\Engine\Fantasya\Message\Unit\TakeOfferPaymentMessage;
use Lemuria\Id;
use Lemuria\Model\Exception\NotRegisteredException;
use Lemuria\Model\Fantasya\Composition;
use Lemuria\Model\Fantasya\Extension\Valuables;
use Lemuria\Model\Fantasya\Factory\BuilderTrait;
use Lemuria\Model\Fantasya\Practice;
use Lemuria\Model\Fantasya\Quantity;
use Lemuria\Model\Fantasya\Unicum;
use Lemuria\Model\Fantasya\Unit;

trait UnicumTrait
{
	use BuilderTrait;
	use MessageTrait;

	protected ?Unicum $unicum;

	protected ?Composition $composition;

	private readonly int $argumentIndex;

	public function ArgumentIndex(): int {
		return $this->argumentIndex;
	}

	public function Unicum(): Unicum {
		return $this->unicum;
	}

	private function parseUnicum(): string {
		$n = $this->phrase->count();
		if ($n === 1) {
			$id                  = $this->phrase->getParameter();
			$this->unicum        = $this->getUnicum($id);
			$this->composition   = $this->unicum?->Composition();
		} elseif ($n === 2) {
			$this->composition   = $this->context->Factory()->composition($this->phrase->getParameter());
			$id                  = $this->phrase->getParameter(2);
			$this->unicum        = $this->getUnicum($id);
		} else {
			throw new InvalidCommandException($this);
		}
		return $id;
	}

	private function findUnicum(): string {
		$n = $this->phrase->count();
		if ($n === 1) {
			$id                  = $this->phrase->getParameter();
			$this->unicum        = $this->searchUnicum($id);
			$this->composition   = $this->unicum?->Composition();
		} elseif ($n === 2) {
			$this->composition   = $this->context->Factory()->composition($this->phrase->getParameter());
			$id                  = $this->phrase->getParameter(2);
			$this->unicum        = $this->searchUnicum($id);
		} else {
			throw new InvalidCommandException($this);
		}
		return $id;
	}

	private function isOfferedUnicum(): string {
		$n = $this->phrase->count();
		if ($n === 1) {
			$id                  = $this->phrase->getParameter();
			$this->unicum        = $this->getOfferedUnicum($id);
			$this->composition   = $this->unicum?->Composition();
		} elseif ($n === 2) {
			$this->composition   = $this->context->Factory()->composition($this->phrase->getParameter());
			$id                  = $this->phrase->getParameter(2);
			$this->unicum        = $this->getOfferedUnicum($id);
		} else {
			throw new InvalidCommandException($this);
		}
		return $id;
	}

	private function parseUnicumWithArguments(): void {
		$mapper = $this->context->UnicumMapper();
		$id     = $this->phrase->getParameter();
		if ($mapper->has($id)) {
			$id = $mapper->get($id);
		}
		$unicum = $this->getUnicum($id);
		if ($unicum) {
			$this->unicum        = $unicum;
			$this->composition   = $unicum->Composition();
			$this->argumentIndex = 2;
		} else {
			$id     = $this->phrase->getParameter(2);
			$unicum = $this->getUnicum($id);
			if ($unicum) {
				$this->unicum        = $unicum;
				$this->composition   = $unicum->Composition();
				$this->argumentIndex = 3;
			} else {
				throw new InvalidCommandException($this);
			}
		}
	}

	private function findUnicumWithArguments(): string {
		$id     = $this->phrase->getParameter();
		$unicum = $this->searchUnicum($id);
		if ($unicum) {
			$this->unicum        = $unicum;
			$this->composition   = $unicum->Composition();
			$this->argumentIndex = 2;
		} elseif ($this->phrase->count() >= 2) {
			$id     = $this->phrase->getParameter(2);
			$unicum = $this->searchUnicum($id);
			if ($unicum) {
				$this->unicum        = $unicum;
				$this->composition   = $unicum->Composition();
				$this->argumentIndex = 3;
			} else {
				throw new InvalidCommandException($this);
			}
		} else {
			$this->unicum = null;
		}
		return $id;
	}

	private function getUnicum(string|Id $id): ?Unicum {
		if (is_string($id)) {
			$id = Id::fromId($id);
		}
		$treasury = $this->unit->Treasury();
		if ($treasury->has($id)) {
			return $treasury[$id];
		}
		return null;
	}

	private function searchUnicum(string|Id $id): ?Unicum {
		if (is_string($id)) {
			$id = Id::fromId($id);
		}
		$treasury = $this->unit->Construction()?->Treasury();
		if ($treasury && $treasury->has($id)) {
			return $treasury[$id];
		}
		$treasury = $this->unit->Vessel()?->Treasury();
		if ($treasury && $treasury->has($id)) {
			return $treasury[$id];
		}
		$treasury = $this->unit->Region()->Treasury();
		if ($treasury->has($id)) {
			return $treasury[$id];
		}
		return null;
	}

	private function getOfferedUnicum(string $id): ?Unicum {
		$id = Id::fromId($id);
		try {
			$unicum = Unicum::get($id);
		} catch (NotRegisteredException) {
			return null;
		}

		$collector = $unicum->Collector();
		if ($collector instanceof Unit && $collector->Extensions()->offsetExists(Valuables::class)) {
			/** @var Valuables $valuables */
			$valuables = $collector->Extensions()->offsetGet(Valuables::class);
			if ($valuables->has($id)) {
				return $unicum;
			}
		}
		return null;
	}

	private function buyUnicumFromMerchant(): bool {
		$composition = $this->phrase->getParameter();
		if ($this->context->Factory()->isComposition($composition)) {
			$this->composition = $this->context->Factory()->composition($composition);
			$id                = $this->phrase->getParameter(2);
			$this->unicum      = $this->getOfferedUnicum($id);
			if ($this->unicum?->Composition() !== $this->composition) {
				return false;
			}
			$this->argumentIndex = 3;
		} else {
			$id           = $this->phrase->getParameter();
			$this->unicum = $this->getOfferedUnicum($id);
			if (!$this->unicum) {
				return false;
			}
			$this->composition   = $this->unicum->Composition();
			$this->argumentIndex = 2;
		}
		$offer     = null;
		$amount    = (int)$this->phrase->getParameter($this->argumentIndex);
		$commodity = $this->phrase->getParameter($this->argumentIndex + 1);
		if ($commodity) {
			$offer = new Quantity(self::createCommodity($commodity), $amount);
		}

		/** @var Unit $merchant */
		$merchant = $this->unicum->Collector();
		/** @var Valuables $valuables */
		$valuables = $merchant->Extensions()->offsetGet(Valuables::class);
		$price     = $valuables->getPrice($this->unicum);
		if ($offer) {
			if (!$this->context->getTurnOptions()->IsSimulation() && ($offer->Commodity() !== $price->Commodity() || $offer->Count() < $price->Minimum())) {
				$this->message(TakeOfferPaymentMessage::class, $this->unit)->e($this->unicum)->e($merchant, TakeOfferPaymentMessage::UNIT);
				return true;
			}
			$quantity = new Quantity($offer->Commodity(), $offer->Count());
		} else {
			$quantity = new Quantity($price->Commodity(), $price->Maximum());
		}

		$payment = $this->context->getResourcePool($this->unit)->take($this->unit, $quantity);
		if ($payment->Count() < $price->Minimum()) {
			$this->message(TakeNotEnoughMessage::class, $this->unit)->e($this->unicum)->s($payment->Commodity());
			return true;
		}
		$this->unit->Inventory()->remove(new Quantity($payment->Commodity(), $payment->Count()));
		$merchant->Inventory()->add(new Quantity($payment->Commodity(), $payment->Count()));
		$merchant->Treasury()->remove($this->unicum);
		$this->unit->Treasury()->add($this->unicum);
		$this->message(TakeBoughtMessage::class, $this->unit)->e($this->unicum)->e($merchant, TakeOfferPaymentMessage::UNIT)->i($payment);
		$this->message(TakeOfferMessage::class, $merchant)->e($this->unicum)->e($this->unit, TakeOfferPaymentMessage::UNIT)->i($payment);
		return true;
	}

	private function getOperate(Practice $practice): AbstractOperate {
		$operate     = $this->context->Factory()->operateUnicum($this->unicum, $this);
		$id          = (string)$this->unicum->Id();
		$composition = $this->unicum->Composition();
		$this->message(OperatePracticeMessage::class, $this->unit)->p($id)->s($composition)->p($practice->name, OperatePracticeMessage::PRACTICE);
		return $operate;
	}
}
