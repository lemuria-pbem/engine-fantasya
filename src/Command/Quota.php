<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Command;

use Lemuria\Engine\Fantasya\Exception\InvalidCommandException;
use Lemuria\Engine\Fantasya\Message\Unit\QuotaNoHerbageMessage;
use Lemuria\Engine\Fantasya\Message\Unit\QuotaNoHerbMessage;
use Lemuria\Engine\Fantasya\Message\Unit\QuotaNotSetMessage;
use Lemuria\Engine\Fantasya\Message\Unit\QuotaRemoveHerbMessage;
use Lemuria\Engine\Fantasya\Message\Unit\QuotaRemoveMessage;
use Lemuria\Engine\Fantasya\Message\Unit\QuotaSetHerbMessage;
use Lemuria\Engine\Fantasya\Message\Unit\QuotaSetMessage;
use Lemuria\Engine\Fantasya\Message\Unit\QuotaUnknownHerbageMessage;
use Lemuria\Model\Fantasya\Commodity;
use Lemuria\Model\Fantasya\Herb;
use Lemuria\Model\Fantasya\Commodity\Peasant;
use Lemuria\Model\Fantasya\Commodity\Wood;
use Lemuria\Model\Fantasya\Factory\BuilderTrait;
use Lemuria\Model\Fantasya\Quantity;
use Lemuria\Model\Fantasya\Quota as Model;
use Lemuria\Model\Fantasya\Quotas;

/**
 * The Quota command sets ressource production limits on regions.
 *
 * GRENZE <amount> <commodity>
 * GRENZE <commodity> <amount>
 * GRENZE <commodity> Nicht
 * GRENZE <percent> Kräuter
 * GRENZE Kräuter <percent>
 * GRENZE Kräuter Nicht
 */
class Quota extends UnitCommand
{
	use BuilderTrait;

	private const HERB = ['kräuter', 'kraeuter'];

	private const PEASANT = ['bauer', 'bauern'];

	private const TREE = ['baum', 'bäume'];

	private Quotas $quotas;

	protected function initialize(): void {
		parent::initialize();
		$region       = $this->unit->Region();
		$regulation   = $this->unit->Party()->Regulation();
		$this->quotas = $regulation->add($region)->getQuotas($region);
	}

	protected function run(): void {
		if ($this->phrase->count() !== 2) {
			throw new InvalidCommandException($this);
		}

		$commodity = $this->parseCommodity();
		$amount    = $this->parseAmount();
		if ($amount === 'nicht') {
			if (in_array($commodity, self::HERB)) {
				$this->removeHerbQuota();
			} elseif (in_array($commodity, self::PEASANT)) {
				$this->removeQuota(self::createCommodity(Peasant::class));
			} elseif (in_array($commodity, self::TREE)) {
				$this->removeQuota(self::createCommodity(Wood::class));
			} else {
				$this->removeQuota($this->context->Factory()->commodity($commodity));
			}
		} else {
			$value = (int)$amount;
			if (in_array($commodity, self::HERB)) {
				if ($value . '%' !== $amount) {
					throw new InvalidCommandException($this, 'Quota must be percentage.');
				}
				$this->setHerbQuota($value / 100);
			} else {
				if ((string)$value !== $amount) {
					throw new InvalidCommandException($this, 'Quota must be number.');
				}
				if (in_array($commodity, self::PEASANT)) {
					$this->setQuota($value, self::createCommodity(Peasant::class));
				} elseif (in_array($commodity, self::TREE)) {
					$this->setQuota($value, self::createCommodity(Wood::class));
				} else {
					$this->setQuota($value, $this->context->Factory()->commodity($commodity));
				}
			}
		}
	}

	protected function setQuota(int $amount, Commodity $commodity): void {
		$quota = $this->quotas->getQuota($commodity);
		if ($quota) {
			$quota->setThreshold($amount);
		} else {
			$this->quotas->add(new Model($commodity, $amount));
		}
		$region = $this->unit->Region();
		$quota  = new Quantity($commodity, $amount);
		$this->message(QuotaSetMessage::class)->e($region)->i($quota);
	}

	protected function setHerbQuota(float $percentage): void {
		$region     = $this->unit->Region();
		$herbalBook = $this->unit->Party()->HerbalBook();
		if ($herbalBook->offsetExists($region->Id())) {
			$herb = $herbalBook->getHerbage($region)?->Herb();
			if ($herb) {
				foreach ($this->getQuotasHerbs() as $commodity) {
					$this->quotas->offsetUnset($commodity);
				}
				$this->quotas->add(new Model($herb, $percentage));
				$this->message(QuotaSetHerbMessage::class)->e($region)->p($percentage);
			} else {
				$this->message(QuotaNoHerbageMessage::class)->e($region);
			}
		} else {
			$this->message(QuotaUnknownHerbageMessage::class)->e($region);
		}
	}

	protected function removeQuota(Commodity $commodity): void {
		$region = $this->unit->Region();
		if ($this->quotas->offsetExists($commodity)) {
			$this->quotas->offsetUnset($commodity);
			$this->message(QuotaRemoveMessage::class)->e($region)->s($commodity);
		} else {
			$this->message(QuotaNotSetMessage::class)->e($region)->s($commodity);
		}
	}

	protected function removeHerbQuota(): void {
		$region = $this->unit->Region();
		$herbs  = $this->getQuotasHerbs();
		if (empty($herbs)) {
			$this->message(QuotaNoHerbMessage::class)->e($region);
		} else {
			foreach ($herbs as $commodity) {
				$this->quotas->offsetUnset($commodity);
			}
			$this->message(QuotaRemoveHerbMessage::class)->e($region);
		}
	}

	private function parseCommodity(): string {
		$amount = mb_strtolower($this->phrase->getParameter());
		$number = (int)$amount;
		if ((string)$number === $amount || ($number . '%') === $amount) {
			return mb_strtolower($this->phrase->getParameter(2));
		}
		return mb_strtolower($this->phrase->getParameter());
	}

	private function parseAmount(): string {
		$amount = mb_strtolower($this->phrase->getParameter());
		$number = (int)$amount;
		if ((string)$number === $amount || ($number . '%') === $amount) {
			return $amount;
		}
		return mb_strtolower($this->phrase->getParameter(2));
	}

	private function getQuotasHerbs(): array {
		$herbs = [];
		foreach ($this->quotas as $quota) {
			$commodity = $quota->Commodity();
			if ($commodity instanceof Herb) {
				$herbs[] = $commodity;
			}
		}
		return $herbs;
	}
}
