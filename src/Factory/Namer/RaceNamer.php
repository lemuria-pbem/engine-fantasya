<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Factory\Namer;

use function Lemuria\getClass;

use Lemuria\Engine\Fantasya\Factory\Namer;
use Lemuria\Model\Dictionary;
use Lemuria\Model\Fantasya\Unit;

class RaceNamer implements Namer
{
	protected Dictionary $dictionary;

	public function __construct() {
		$this->dictionary = new Dictionary();
	}

	public function name(Unit $unit): Unit {
		$race  = getClass($unit->Race());
		$index = $unit->Size() > 1 ? 1 : 0;
		$name  = $this->dictionary->get('race.' . $race, $index);
		$unit->setName($name);
		return $unit;
	}
}
