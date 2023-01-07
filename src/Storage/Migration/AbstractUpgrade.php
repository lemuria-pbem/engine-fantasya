<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Storage\Migration;

use Lemuria\Model\Game;
use Lemuria\Model\Fantasya\Storage\Migration\AbstractUpgrade as AbstractModelUpgrade;

abstract class AbstractUpgrade extends AbstractModelUpgrade
{
	private const NAMESPACE = __NAMESPACE__ . '\\Upgrade\\';

	public static function getAll(): array {
		$upgrades = [];
		foreach (glob(__DIR__ . '/Upgrade/*.php') as $path) {
			$file       = basename($path);
			$class      = substr($file, 0, strlen($file) - 4);
			$upgrades[] = self::NAMESPACE . $class;
		}
		return $upgrades;
	}

	public function __construct(Game $game) {
		parent::__construct($game);
	}
}
