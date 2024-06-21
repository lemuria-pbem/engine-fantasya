<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Factory\Model;

final class TimerEvent
{
	public function __construct(public string $class, public ?array $options = null) {
	}
}
