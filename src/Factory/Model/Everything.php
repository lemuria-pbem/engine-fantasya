<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Lemuria\Factory\Model;

use JetBrains\PhpStorm\Pure;

use Lemuria\Model\Lemuria\Commodity;
use Lemuria\SingletonTrait;

final class Everything implements Commodity
{
	use SingletonTrait;

	/**
	 * Get the weight of a product.
	 */
	#[Pure] public function Weight(): int {
		return 0;
	}
}
