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
	/**
	 * @type array<string>
	 */
	private const array GROUPS = [
		'Message\\Construction',
		'Message\\Party', 'Message\\Party\\Administrator', 'Message\\Party\\Event',
		'Message\\Region', 'Message\\Region\\Event',
		'Message\\Unit', 'Message\\Unit\\Act', 'Message\\Unit\\Apply', 'Message\\Unit\\Cast', 'Message\\Unit\\Event', 'Message\\Unit\\Operate',
		'Message\\Vessel'
	];

	/**
	 * @return array<SingletonGroup>
	 */
	public function getGroups(): array {
		$groups = [];
		foreach (self::GROUPS as $group) {
			$groups[] = new SingletonGroup($group, __NAMESPACE__, __DIR__);
		}
		return $groups;
	}
}
