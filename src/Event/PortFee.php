<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Event;

use Lemuria\Engine\Fantasya\Context;
use Lemuria\Engine\Fantasya\Effect\UnpaidDemurrage;
use Lemuria\Engine\Fantasya\Factory\CollectTrait;
use Lemuria\Engine\Fantasya\Message\Unit\PortFeeMessage;
use Lemuria\Engine\Fantasya\Message\Unit\PortFeeNotPaidMessage;
use Lemuria\Engine\Fantasya\Message\Unit\PortFeeOnlyMessage;
use Lemuria\Engine\Fantasya\Message\Unit\PortFeePaidMessage;
use Lemuria\Engine\Fantasya\Message\Unit\PortFeePaidOnlyMessage;
use Lemuria\Engine\Fantasya\Message\Unit\UnpaidDemurrageMessage;
use Lemuria\Engine\Fantasya\Priority;
use Lemuria\Engine\Fantasya\State;
use Lemuria\Lemuria;
use Lemuria\Model\Fantasya\Building\Port;
use Lemuria\Model\Fantasya\Commodity;
use Lemuria\Model\Fantasya\Construction;
use Lemuria\Model\Fantasya\Extension\Fee;
use Lemuria\Model\Fantasya\Quantity;
use Lemuria\Model\Fantasya\Unit;
use Lemuria\Model\Fantasya\Vessel;

/**
 * Ship captains pay the port fee.
 */
final class PortFee extends AbstractEvent
{
	use CollectTrait;

	public function __construct(State $state) {
		parent::__construct($state, Priority::Before);
		$this->context = new Context($state);
	}

	protected function run(): void {
		foreach (Construction::all() as $construction) {
			if ($construction->Building() instanceof Port) {
				$master     = $construction->Inhabitants()->Owner();
				$extensions = $construction->Extensions();
				/** @var Fee $fee */
				$fee         = $extensions[Fee::class];
				$quantity    = $fee->Fee();
				$feePerPoint = $quantity?->Count();
				if ($feePerPoint > 0) {
					foreach ($construction->Region()->Fleet() as $vessel) {
						if ($vessel->Port() === $construction) {
							$captain   = $vessel->Passengers()->Owner();
							$totalFee  = $vessel->Ship()->Captain() * $feePerPoint;
							$unpaidFee = $this->pay($captain, $quantity->Commodity(), $totalFee, $master);
							if ($unpaidFee) {
								$this->getUnpaidDemurrage($vessel)->Demurrage()->add($unpaidFee);
								$this->message(UnpaidDemurrageMessage::class, $captain)->i($unpaidFee);
								$this->message(PortFeeNotPaidMessage::class, $master)->e($captain)->i($unpaidFee);
							}
						}
					}
				}
			}
		}
	}

	private function pay(?Unit $captain, Commodity $commodity, int $amount, ?Unit $master): ?Quantity {
		if ($captain && $master && $captain->Party() !== $master->Party()) {
			$payment = $this->collectQuantity($captain, $commodity, $amount);
			$paid    = $payment->Count();
			if ($paid > 0) {
				$captain->Inventory()->remove($payment);
				$master->Inventory()->add(new Quantity($commodity, $amount));
				if ($paid < $amount) {
					$this->message(PortFeeOnlyMessage::class, $captain)->i($payment);
					$this->message(PortFeePaidOnlyMessage::class, $master)->e($captain)->i($payment);
				} else {
					$this->message(PortFeeMessage::class, $captain)->i($payment);
					$this->message(PortFeePaidMessage::class, $master)->e($captain)->i($payment);
				}
			}
			$unpaid = $amount - $paid;
			if ($unpaid > 0) {
				return new Quantity($commodity, $unpaid);
			}
		}
		return null;
	}

	private function getUnpaidDemurrage(Vessel $vessel): UnpaidDemurrage {
		$effect   = new UnpaidDemurrage(State::getInstance());
		$existing = Lemuria::Score()->find($effect->setVessel($vessel));
		if ($existing instanceof UnpaidDemurrage) {
			return $existing;
		}
		Lemuria::Score()->add($effect);
		return $effect;
	}
}
