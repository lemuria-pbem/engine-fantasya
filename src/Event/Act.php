<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Event;

interface Act
{
	public function act(): static;
}
