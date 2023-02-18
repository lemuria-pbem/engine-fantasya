<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Factory\Namer;

use function Lemuria\getClass;
use Lemuria\Entity;
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

	public function name(Domain|Identifiable|Entity $entity): string {
		if (!$entity instanceof Unit) {
			throw new LemuriaException('This namer is not intended to be used for ' . $entity->Catalog()->name . ' entities.');
		}

		$race  = getClass($entity->Race());
		$index = $entity->Size() > 1 ? 1 : 0;
		$name  = $this->dictionary->get('race.' . $race, $index);
		$entity->setName($name);
		return $name;
	}
}
