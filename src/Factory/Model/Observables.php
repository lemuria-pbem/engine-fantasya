<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Factory\Model;

use Lemuria\Model\Fantasya\Commodity;
use Lemuria\Model\Fantasya\Protection;
use Lemuria\Model\Fantasya\Quantity;
use Lemuria\Model\Fantasya\Resources;
use Lemuria\Model\Fantasya\Transport;
use Lemuria\Model\Fantasya\Weapon;

class Observables extends Resources
{
	public static function isObservable(Commodity $commodity): bool {
		return $commodity instanceof Transport || $commodity instanceof Weapon || $commodity instanceof Protection;
	}

	public function __construct(Resources $resources) {
		foreach ($resources as $quantity) {
			$commodity = $quantity->Commodity();
			if (self::isObservable($commodity)) {
				$this->add(new Quantity($commodity, $quantity->Count()));
			}
		}
	}
}
