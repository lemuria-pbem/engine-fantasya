<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Factory\Model;

use Lemuria\Model\Fantasya\Commodity\Weapon\Catapult;
use Lemuria\Model\Fantasya\Commodity;
use Lemuria\Model\Fantasya\Quantity;
use Lemuria\Model\Fantasya\Resources;
use Lemuria\Model\Fantasya\Transport;

class Observables extends Resources
{
	public static function isObservable(Commodity $commodity): bool {
		if ($commodity instanceof Transport) {
			return true;
		}
		if ($commodity instanceof Catapult) {
			return true;
		}
		return false;
	}

	public function __construct(Resources $resources) {
		foreach ($resources as $quantity /* @var Quantity $quantity */) {
			$commodity = $quantity->Commodity();
			if (self::isObservable($commodity)) {
				$this->add(new Quantity($commodity, $quantity->Count()));
			}
		}
	}
}
