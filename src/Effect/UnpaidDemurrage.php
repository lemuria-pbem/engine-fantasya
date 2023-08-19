<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Effect;

use Lemuria\Engine\Fantasya\Factory\CollectTrait;
use Lemuria\Engine\Fantasya\Message\Unit\UnpaidDemurrageMessage;
use Lemuria\Engine\Fantasya\Message\Unit\UnpaidDemurragePaidMessage;
use Lemuria\Engine\Fantasya\Priority;
use Lemuria\Engine\Fantasya\State;
use Lemuria\Exception\UnserializeEntityException;
use Lemuria\Lemuria;
use Lemuria\Model\Fantasya\Quantity;
use Lemuria\Model\Fantasya\Resources;
use Lemuria\Validate;

final class UnpaidDemurrage extends AbstractVesselEffect
{
	use CollectTrait;

	private const DEMURRAGE = 'demurrage';

	private Resources $demurrage;

	public function __construct(State $state) {
		parent::__construct($state, Priority::Before);
		$this->demurrage = new Resources();
	}

	public function Demurrage(): Resources {
		return $this->demurrage;
	}

	public function serialize(): array {
		$data                  = parent::serialize();
		$data[self::DEMURRAGE] = $this->demurrage->serialize();
		return $data;
	}

	public function unserialize(array $data): static {
		parent::unserialize($data);
		$this->demurrage->unserialize($data[self::DEMURRAGE]);
		return $this;
	}

	/**
	 * @throws UnserializeEntityException
	 */
	protected function validateSerializedData(array $data): void {
		parent::validateSerializedData($data);
		$this->validate($data, self::DEMURRAGE, Validate::Array);
	}

	protected function run(): void {
		$captain  = $this->Vessel()->Passengers()->Owner();
		$payments = new Resources();
		foreach ($this->demurrage as $quantity) {
			$amount  = $quantity->Count();
			$payment = $this->collectQuantity($captain, $quantity->Commodity(), $amount);
			$payed   = $payment->Count();
			if ($payed > 0) {
				$payments->add($payment);
				if ($payed < $amount) {
					$remaining = new Quantity($quantity->Commodity(), $amount - $payed);
					$this->message(UnpaidDemurrageMessage::class, $captain)->i($remaining);
				} else {
					$this->message(UnpaidDemurragePaidMessage::class, $captain)->i($quantity);
				}
			} else {
				$this->message(UnpaidDemurrageMessage::class, $captain)->i($quantity);
			}
		}

		foreach ($payments as $payment) {
			$this->demurrage->remove($payment);
		}
		if ($payments->isEmpty()) {
			Lemuria::Score()->remove($this);
		}
	}
}
