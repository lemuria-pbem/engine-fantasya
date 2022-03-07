<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Factory;

use JetBrains\PhpStorm\Pure;

trait OneActivityTrait
{
	use DefaultActivityTrait;

	#[Pure] public function Activity(): string {
		return microtime();
	}
}
