<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Event;

use Lemuria\Engine\Fantasya\Context;
use Lemuria\Engine\Fantasya\Effect\UnpaidFee;
use Lemuria\Engine\Fantasya\Factory\CollectTrait;
use Lemuria\Engine\Fantasya\Message\Unit\MarketFeeBannedMessage;
use Lemuria\Engine\Fantasya\Message\Unit\MarketFeeNotPaidMessage;
use Lemuria\Engine\Fantasya\Message\Unit\MarketFeePaidMessage;
use Lemuria\Engine\Fantasya\Message\Unit\MarketFeeReceivedMessage;
use Lemuria\Engine\Fantasya\Priority;
use Lemuria\Engine\Fantasya\State;
use Lemuria\Lemuria;
use Lemuria\Model\Fantasya\Commodity;
use Lemuria\Model\Fantasya\Construction;
use Lemuria\Model\Fantasya\Extension\Market;
use Lemuria\Model\Fantasya\Quantity;
use Lemuria\Model\Fantasya\Unit;

/**
 * Merchants pay the fixed market fee.
 */
final class MarketFee extends AbstractEvent
{
	use CollectTrait;

	public function __construct(State $state) {
		parent::__construct($state, Priority::Before);
		$this->context = new Context($state);
	}

	protected function run(): void {
		foreach (Construction::all() as $construction) {
			$extensions = $construction->Extensions();
			$market     = $extensions[Market::class] ?? null;
			if ($market instanceof Market) {
				$fee = $market->Fee();
				if ($fee instanceof Quantity) {
					$inhabitants = $construction->Inhabitants();
					$owner       = $inhabitants->Owner();
					$nonPayers   = [];
					foreach ($inhabitants as $unit) {
						if ($unit !== $owner) {
							if (!$this->pay($unit, $fee->Commodity(), $fee->Count(), $owner)) {
								$nonPayers[] = $unit;
							}
						}
					}
					foreach ($nonPayers as $unit) {
						$effect = new UnpaidFee($this->state);
						Lemuria::Score()->add($effect->setUnit($unit));
						$this->message(MarketFeeNotPaidMessage::class, $unit);
						$this->message(MarketFeeBannedMessage::class, $owner)->e($unit);
					}
				}
			}
		}
	}

	private function pay(Unit $unit, Commodity $commodity, int $amount, Unit $owner): bool {
		$payment = $this->collectQuantity($unit, $commodity, $amount);
		if ($payment->Count() < $amount) {
			return false;
		}
		$unit->Inventory()->remove($payment);
		$owner->Inventory()->add(new Quantity($commodity, $amount));
		$this->message(MarketFeePaidMessage::class, $unit)->e($owner)->i($payment);
		$this->message(MarketFeeReceivedMessage::class, $owner)->e($unit)->i($payment);
		return true;
	}
}
