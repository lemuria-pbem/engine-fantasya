<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Command;

use Lemuria\Engine\Fantasya\Activity;
use Lemuria\Engine\Fantasya\Exception\UnknownCommandException;
use Lemuria\Engine\Fantasya\Factory\OneActivityTrait;

/**
 * Explore the region to find herbs.
 *
 * - ERFORSCHEN
 * - ERFORSCHEN Kraut|Kräuter|Kraeuter
 * - FORSCHEN
 * - FORSCHEN Kraut|Kräuter|Kraeuter
 */
final class Explore extends UnitCommand implements Activity
{
	use OneActivityTrait;

	protected function initialize(): void {
		parent::initialize();
		throw new UnknownCommandException($this);
	}

	protected function run(): void {
		$n = $this->phrase->count();
		if ($n > 1) {
			throw new UnknownCommandException($this);
		}
		if ($n === 1) {
			switch (strtolower($this->phrase->getParameter())) {
				case 'kraut' :
				case 'kräuter' :
				case 'kraeuter' :
					break;
				default :
					throw new UnknownCommandException($this);
			}
		}

		//TODO
	}
}
