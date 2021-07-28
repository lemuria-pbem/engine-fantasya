<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Factory\Model;

use Lemuria\Id;
use Lemuria\Model\Fantasya\Party;

final class DisguisedParty extends Party
{
	public function __construct() {
		parent::__construct();
		$this->setName('Unknown party');
	}

	public function Id(): Id {
		return new Id(0);
	}
}
