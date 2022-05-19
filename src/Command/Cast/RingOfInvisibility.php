<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Command\Cast;

use Lemuria\Engine\Fantasya\Effect\Enchantment as EnchantmentEffect;
use Lemuria\Engine\Fantasya\Factory\MessageTrait;
use Lemuria\Engine\Fantasya\Message\Unit\Cast\RingOfInvisibilityGoldRingMessage;
use Lemuria\Engine\Fantasya\State;
use Lemuria\Lemuria;
use Lemuria\Model\Fantasya\Commodity\Jewelry\GoldRing;
use Lemuria\Model\Fantasya\Enchantment;
use Lemuria\Model\Fantasya\Factory\BuilderTrait;
use Lemuria\Model\Fantasya\Quantity;

final class RingOfInvisibility extends AbstractCast
{
	use BuilderTrait;
	use MessageTrait;

	public function cast(): void {
		$unit      = $this->cast->Unit();
		$inventory = $unit->Inventory();
		$ring      = self::createCommodity(GoldRing::class);
		if ($inventory->offsetExists($ring)) {
			$unit->Aura()->consume($this->cast->Aura());
			$inventory->remove(new Quantity($ring));
			$effect   = new EnchantmentEffect(State::getInstance());
			$existing = Lemuria::Score()->find($effect->setUnit($unit));
			if ($existing) {
				$effect = $existing;
			} else {
				Lemuria::Score()->add($effect);
			}
			$effect->Enchantments()->add(new Enchantment($this->cast->Spell()));
		} else {
			$this->message(RingOfInvisibilityGoldRingMessage::class, $unit);
		}
	}
}
