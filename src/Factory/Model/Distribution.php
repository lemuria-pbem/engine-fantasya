<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Factory\Model;

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
}
