<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Factory\Queue;

interface Shuffle
{
	public function shuffle(array $queue): array;
}
