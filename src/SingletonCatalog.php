<?php
declare (strict_types = 1);
namespace Lemuria\Engine\Fantasya;

use Lemuria\Factory\SingletonCatalog as SingletonCatalogInterface;
use Lemuria\Factory\SingletonGroup;

/**
 * A map of Lemuria Singleton classes used for instantiation.
 */
class SingletonCatalog implements SingletonCatalogInterface
{
	private const GROUPS = [
		'Message\\Construction', 'Message\\Party', 'Message\\Region', 'Message\\Unit', 'Message\\Vessel'
	];

	/**
	 * @return SingletonGroup[]
	 */
	public function getGroups(): array {
		$groups = [];
		foreach (self::GROUPS as $group) {
			$groups[] = new SingletonGroup($group, __NAMESPACE__, __DIR__);
		}
		return $groups;
	}
}
