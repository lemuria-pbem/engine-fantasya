<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Event;

use Lemuria\Model\Fantasya\Unit;

interface Behaviour
{
	public function __construct(Unit $unit);

	public function Unit(): Unit;

	public function Reproduction(): Reproduction;

	public function prepare(): static;

	public function conduct(): static;

	public function finish(): static;
}
