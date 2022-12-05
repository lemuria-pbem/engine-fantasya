<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Factory\Namer;

use function Lemuria\getClass;
use Lemuria\Exception\LemuriaException;
use Lemuria\Factory\Namer;
use Lemuria\Identifiable;
use Lemuria\Model\Domain;
use Lemuria\Model\Dictionary;
use Lemuria\Model\Fantasya\Unit;

class RaceNamer implements Namer
{
	protected readonly Dictionary $dictionary;

	public function __construct() {
		$this->dictionary = new Dictionary();
	}

	public function name(Domain|Identifiable $entity): string {
		if ($entity instanceof Unit) {
			$race  = getClass($entity->Race());
			$index = $entity->Size() > 1 ? 1 : 0;
			return $this->dictionary->get('race.' . $race, $index);
		}
		throw new LemuriaException('This namer is not intended to be used for ' . $entity->Catalog()->name . ' entities.');
	}
}
