<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Factory\Model;

use Lemuria\Item;
use Lemuria\Singleton;

final class Job extends Item
{
	private bool $hasCount = true;

	public function __construct(Singleton $object, int $count = 0, private readonly int|float|null $threshold = null) {
		if ($count <= 0) {
			$count = PHP_INT_MAX;
			$this->hasCount = false;
		}
		parent::__construct($object, $count);
	}

	public function Threshold(): int|float|null {
		return $this->threshold;
	}

	public function hasCount(): bool {
		return $this->hasCount;
	}

	public function hasThreshold(): bool {
		return $this->threshold !== null;
	}
}
