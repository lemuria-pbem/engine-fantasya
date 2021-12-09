<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Command\Cast;

use Lemuria\Engine\Fantasya\Factory\CamouflageTrait;
use Lemuria\Engine\Fantasya\Factory\GiftTrait;
use Lemuria\Engine\Fantasya\Message\Unit\Cast\AuraTransferFailedMessage;
use Lemuria\Engine\Fantasya\Message\Unit\Cast\AuraTransferImpossibleMessage;
use Lemuria\Engine\Fantasya\Message\Unit\Cast\AuraTransferMessage;
use Lemuria\Engine\Fantasya\Message\Unit\Cast\AuraTransferNoMagicianMessage;
use Lemuria\Engine\Fantasya\Message\Unit\Cast\AuraTransferNotFound;
use Lemuria\Engine\Fantasya\Message\Unit\Cast\AuraTransferReceivedMessage;
use Lemuria\Engine\Fantasya\Message\Unit\Cast\AuraTransferRejectedMessage;
use Lemuria\Exception\LemuriaException;
use Lemuria\Model\Fantasya\Factory\BuilderTrait;
use Lemuria\Model\Fantasya\Talent\Magic;

final class AuraTransfer extends AbstractCast
{
	use BuilderTrait;
	use CamouflageTrait;
	use GiftTrait;

	private const RATE = 2.0;

	public function cast(): void {
		$unit      = $this->cast->Unit();
		$aura      = $this->cast->Aura();
		$available = $unit->Aura()->Aura();
		$demand    = (int)ceil(self::RATE * $aura);
		if ($demand > $available) {
			$transfer  = (int)floor($available / self::RATE);
			$reduction = (int)ceil(self::RATE * $transfer);
		} else {
			$transfer  = $aura;
			$reduction = $demand;
		}
		if ($transfer <= 0) {
			$this->message(AuraTransferImpossibleMessage::class, $unit);
		}

		$this->recipient = $this->cast->Target();
		$context         = $this->cast->Context();
		$isVisible       = $this->checkVisibility($unit, $this->recipient);
		if (!$this->checkPermission()) {
			if ($isVisible) {
				$this->message(AuraTransferFailedMessage::class, $unit)->e($this->recipient);
				$this->message(AuraTransferRejectedMessage::class, $this->recipient)->e($unit);
				return;
			}
			$this->message(AuraTransferNotFound::class, $unit)->e($this->recipient);
		}
		if (!$isVisible) {
			$this->message(AuraTransferNotFound::class, $unit)->e($this->recipient);
			return;
		}
		$calculus = $context->getCalculus($this->recipient);
		if ($calculus->knowledge(Magic::class)->Level() <= 0) {
			$this->message(AuraTransferNoMagicianMessage::class, $unit)->e($this->recipient);
			return;
		}

		$target = $this->recipient->Aura();
		if (!$target) {
			throw new LemuriaException('Target has no Aura.');
		}
		$unit->Aura()->consume($reduction);
		$target->setAura($target->Aura() + $transfer);
		$this->message(AuraTransferMessage::class, $unit)->e($this->recipient)->p($transfer)->p($reduction, AuraTransferMessage::COST);
		$this->message(AuraTransferReceivedMessage::class, $this->recipient)->e($unit)->p($transfer);
	}
}
