<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya;

use Lemuria\Model\Fantasya\Luxury;
use Lemuria\Model\Fantasya\Party;
use Lemuria\Model\Fantasya\Resources;

/**
 * Merchants are trade commands that attend in luxury distribution.
 */
interface Merchant extends Command
{
	public const BUY = true;

	public const SELL = !self::BUY;

	/**
	 * Get the type of trade.
	 */
	public function Type(): bool;

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
	 * @return Party[]
	 */
	public function checkBeforeCommerce(): array;
}
