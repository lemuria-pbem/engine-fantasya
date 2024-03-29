<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya;

use Lemuria\Model\Fantasya\Luxury;
use Lemuria\Model\Fantasya\Party;
use Lemuria\Model\Fantasya\Region;
use Lemuria\Model\Fantasya\Resources;
use Lemuria\Model\Fantasya\Unit;

/**
 * Merchants are trade commands that attend in luxury distribution.
 */
interface Merchant extends Command
{
	public final const true BUY = true;

	public final const bool SELL = !self::BUY;

	/**
	 * Get the type of trade.
	 */
	public function Type(): bool;

	/**
	 * Get the trading unit.
	 */
	public function Unit(): Unit;

	/**
	 * Get the resources this merchant wants to trade.
	 */
	public function getGoods(): Resources;

	/**
	 * Try to trade one piece.
	 */
	public function trade(Luxury $good, int $price): bool;

	/**
	 * Check diplomacy between the unit and region owner and guards.
	 *
	 * This method should return the foreign parties that prevent executing the
	 * command.
	 *
	 * @return array<Party>
	 */
	public function checkBeforeCommerce(): array;

	/**
	 * Give a cost estimation to the merchant to allow silver reservation from pool.
	 */
	public function costEstimation(int $cost): static;

	/**
	 * Finish trade, create messages.
	 */
	public function finish(Region $region): static;
}
