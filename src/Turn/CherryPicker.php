<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Turn;

use Lemuria\Id;
use Lemuria\Model\Fantasya\Party;

interface CherryPicker
{
	public function pickParty(Id|Party|int|string $party): bool;

	public function pickPriority(int $priority): bool;
}
