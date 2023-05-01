<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Turn;

use Lemuria\Id;
use Lemuria\Model\Fantasya\Party;

final class DefaultCherryPicker implements CherryPicker
{
	public function pickParty(Id|Party|int|string $party): bool {
		return true;
	}

	public function pickPriority(int $priority): bool {
		return true;
	}
}
