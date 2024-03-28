<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Factory\Model;

use Lemuria\Model\Fantasya\Ability;
use Lemuria\Model\Fantasya\Modification;

class HungerMalus extends Modification
{
	public function __construct(Ability $ability) {
		$modification = (int)floor(-$ability->Level() / 2);
		parent::__construct($ability->Talent(), $modification);
	}
}
