<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Effect;

use Lemuria\Engine\Fantasya\Priority;
use Lemuria\Engine\Fantasya\State;
use Lemuria\Lemuria;

final class UnicumRemoval extends AbstractUnicumEffect
{
	public function __construct(State $state) {
		parent::__construct($state, Priority::Before);
	}

	protected function run(): void {
		Lemuria::Catalog()->reassign($this->Unicum());
		Lemuria::Catalog()->remove($this->Unicum());
		Lemuria::Log()->debug('The ' . $this->Unicum()->Composition() . ' ' . $this->Unicum() . ' has been deleted.');
		Lemuria::Score()->remove($this);
	}
}
