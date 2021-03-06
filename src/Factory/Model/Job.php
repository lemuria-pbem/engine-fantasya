<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Factory\Model;

use JetBrains\PhpStorm\Pure;

use Lemuria\Item;
use Lemuria\Singleton;

final class Job extends Item
{
	private bool $hasCount = true;

	#[Pure] public function __construct(Singleton $object, int $count = 0) {
		if ($count <= 0) {
			$count = PHP_INT_MAX;
			$this->hasCount = false;
		}
		parent::__construct($object, $count);
	}

	public function hasCount(): bool {
		return $this->hasCount;
	}
}
