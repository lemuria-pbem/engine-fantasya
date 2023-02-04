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
			$this->size -= $size;
			foreach ($this as $quantity) {
				$lose->add(new Quantity($quantity->Commodity(), $size * $quantity->Count()));
			}
		}
		return $lose;
	}
}
