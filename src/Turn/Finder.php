<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Turn;

use Lemuria\Engine\Fantasya\Turn\Finder\Party;

class Finder
{
	protected Party $party;

	public function __construct() {
		$this->party = new Party();
	}

	public function Party(): Party {
		return $this->party;
	}
}
