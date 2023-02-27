<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Event\Act;

use Lemuria\Engine\Fantasya\Calculus;
use Lemuria\Engine\Fantasya\Event\Act;
use Lemuria\Engine\Fantasya\Event\ActTrait;
use Lemuria\Engine\Fantasya\Factory\MessageTrait;
use Lemuria\Engine\Fantasya\Message\Unit\Act\ScatterMessage;
use Lemuria\Model\Fantasya\People;
use Lemuria\SortMode;

/**
 * If too many monsters gather in a region, some will leave it next turn.
 */
class Scatter implements Act
{
	use ActTrait;
	use MessageTrait;

	protected ?int $units = null;

	protected ?int $persons = null;

	protected ?People $gathering = null;

	public function act(): Scatter {
		if ($this->checkUnits()) {
			$this->message(ScatterMessage::class, $this->unit);
			$this->createRoamEffect();
			return $this;
		}
		if ($this->checkPersons()) {
			$this->message(ScatterMessage::class, $this->unit);
			$this->createRoamEffect();
			return $this;
		}
		return $this;
	}

	public function setUnits(int $units): Scatter {
		$this->units = $units;
		return $this;
	}

	public function setPersons(int $persons): Scatter {
		$this->persons = $persons;
		return $this;
	}

	protected function initGathering(): void {
		if (!$this->gathering) {
			$calculus        = new Calculus($this->unit);
			$this->gathering = $calculus->getKinsmen()->add($this->unit);
			$this->gathering->sort(SortMode::BySize);
		}
	}

	protected function checkUnits(): bool {
		if ($this->units !== null) {
			$this->initGathering();
			return $this->gathering->count() >= $this->units && $this->gathering->getFirst() === $this->unit;
		}
		return false;
	}

	protected function checkPersons(): bool {
		if ($this->persons !== null) {
			$this->initGathering();
			return $this->gathering->count() > 1 && $this->gathering->Size() >= $this->persons && $this->gathering->getFirst() === $this->unit;
		}
		return false;
	}
}
