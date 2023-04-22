<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Factory\Namer;

use Lemuria\Engine\Fantasya\Factory\GrammarTrait;
use Lemuria\Engine\Fantasya\Message\Casus;
use Lemuria\Entity;
use Lemuria\Exception\LemuriaException;
use Lemuria\Factory\Namer;
use Lemuria\Identifiable;
use Lemuria\Model\Domain;
use Lemuria\Model\Fantasya\Unit;

class RaceNamer implements Namer
{
	use GrammarTrait;

	public function name(Domain|Identifiable|Entity $entity): string {
		if (!$entity instanceof Unit) {
			throw new LemuriaException('This namer is not intended to be used for ' . $entity->Catalog()->name . ' entities.');
		}

		$index = $entity->Size() > 1 ? 1 : 0;
		$name  = $this->translateSingleton($entity->Race(), $index, Casus::Nominative);
		$entity->setName($name);
		return $name;
	}
}
