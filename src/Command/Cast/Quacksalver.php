<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Command\Cast;

use Lemuria\Engine\Fantasya\Message\Unit\Cast\QuacksalverMessage;
use Lemuria\Engine\Fantasya\Message\Unit\Cast\QuacksalverNoneMessage;
use Lemuria\Engine\Fantasya\Message\Unit\Cast\QuacksalverOnlyMessage;
use Lemuria\Model\Fantasya\Commodity\Silver;
use Lemuria\Model\Fantasya\Factory\BuilderTrait;
use Lemuria\Model\Fantasya\Quantity;

final class Quacksalver extends AbstractCast
{
	use BuilderTrait;

	private const SILVER = 50;

	public function cast(): void {
		$unit      = $this->cast->Unit();
		$resources = $unit->Region()->Resources();
		$silver    = self::createCommodity(Silver::class);
		$demand    = $this->cast->Level() * self::SILVER;
		$reserve   = $resources[$silver]->Count();
		$count     = min($demand, $reserve);
		if ($count <= 0) {
			$this->message(QuacksalverNoneMessage::class, $unit);
			return;
		}

		$unit->Aura()->consume($this->cast->Aura());
		$quantity = new Quantity($silver, $count);
		$resources->remove($quantity);
		$unit->Inventory()->add($quantity);
		if ($count < $demand) {
			$this->message(QuacksalverOnlyMessage::class, $unit)->i($quantity);
		} else {
			$this->message(QuacksalverMessage::class, $unit)->i($quantity);
		}
	}
}
