<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Factory\Model;

use Lemuria\Exception\LemuriaException;
use Lemuria\Model\Fantasya\Quantity;
use Lemuria\Model\Fantasya\Resources;

class Distribution extends Resources
{
	protected int $size = 0;

	public function Size(): int {
		return $this->size;
	}

	public function setSize(int $size): Distribution {
		$this->size = $size;
		return $this;
	}

	public function lose(int $size): Resources {
		if ($size > $this->size) {
			throw new LemuriaException('Cannot lose more than ' . $this->size . '.');
		}

		$lose = new Resources();
		if ($this->size > 0) {
			$rate        = $size / $this->size;
			$this->size -= $size;
			foreach ($this as $quantity /* @var Quantity $quantity */) {
				$count = $quantity->Count();
				$lost  = (int)floor($rate * $count);
				if ($lost > 0) {
					$commodity = $quantity->Commodity();
					$this->remove(new Quantity($commodity, $lost));
					$lose->add(new Quantity($commodity, $lost));
				}
			}
		}
		return $lose;
	}
}
