<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Event;

use Lemuria\Model\Fantasya\Unit;

interface Behaviour
{
	public function __construct(Unit $unit);

	public function Unit(): Unit;

	public function prepare(): Behaviour;

	public function conduct(): Behaviour;

	public function finish(): Behaviour;
}
