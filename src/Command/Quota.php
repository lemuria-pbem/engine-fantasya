<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Command;

use Lemuria\Engine\Fantasya\Command\Create\Herb;
use Lemuria\Engine\Fantasya\Exception\InvalidCommandException;
use Lemuria\Model\Fantasya\Commodity;
use Lemuria\Model\Fantasya\Factory\BuilderTrait;
use Lemuria\Model\Fantasya\Quota as Model;
use Lemuria\Model\Fantasya\Quotas;

/**
 * The Quota command sets ressource production limits on regions.
 *
 * GRENZE <amount> <commodity>
 * GRENZE <commodity> Nicht
 * GRENZE <percent> Kräuter
 * GRENZE Kräuter Nicht
 */
class Quota extends UnitCommand
{
	use BuilderTrait;

	private const HERB = ['kräuter', 'kraeuter'];

	private Quotas $quotas;

	protected function initialize(): void {
		parent::initialize();
		$this->quotas = $this->unit->Party()->Regulation()->getQuotas($this->unit->Region());
	}

	protected function run(): void {
		if ($this->phrase->count() !== 2) {
			throw new InvalidCommandException($this);
		}

		$amount = mb_strtolower($this->phrase->getParameter());
		$commodity = mb_strtolower($this->phrase->getParameter(2));
		if ($commodity === 'nicht') {
			if (in_array($amount, self::HERB)) {
				$this->removeHerbQuota();
			} else {
				$this->removeQuota(self::createCommodity($amount));
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
				$this->setQuota($value, self::createCommodity($commodity));
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
		//TODO quota set
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
				//TODO set
			} else {
				//TODO no herbage
			}
		} else {
			//TODO unknown herbage
		}
	}

	protected function removeQuota(Commodity $commodity): void {
		if ($this->quotas->offsetExists($commodity)) {
			$this->quotas->offsetUnset($commodity);
			//TODO removed
		} else {
			//TODO no quota
		}
	}

	protected function removeHerbQuota(): void {
		$herbs = $this->getQuotasHerbs();
		if (empty($herbs)) {
			//TODO no quota
		} else {
			foreach ($herbs as $commodity) {
				$this->quotas->offsetUnset($commodity);
			}
			//TODO removed
		}
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
