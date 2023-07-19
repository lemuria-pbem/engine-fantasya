<?php
declare (strict_types = 1);
namespace Lemuria\Engine\Fantasya\Realm;

use Lemuria\Engine\Fantasya\Merchant;
use Lemuria\Model\Fantasya\Realm;
use Lemuria\Model\Fantasya\Region;
use Lemuria\Engine\Fantasya\State;

/**
 * Helper for central luxury trade in realms.
 */
class Distributor
{
	/**
	 * @var array<int, Region>
	 */
	private array $region;

	/**
	 * @var array<int, int>
	 */
	private array $availability;

	private State $state;

	private Fleet $fleet;

	public function __construct(private readonly Realm $realm) {
		$this->state  = State::getInstance();
		$this->fleet  = State::getInstance()->getRealmFleet($realm);
	}

	public function Realm(): Realm {
		return $this->realm;
	}

	public function distribute(Merchant $merchant): void {
		// 1. Reduce demand if fleet is insufficient.
		// 2. Create Supply for all regions with trade.
		// 3. Create sorted lists of best prices for goods.
		// 4. Plan trade of each good on locations with best price, distribute if more than one location.
		// 5. Give cost estimation to merchant.
		// 6. Trade one by one for each good.
		if ($merchant->Type() === Merchant::BUY) {

		} else {

		}
	}
}
