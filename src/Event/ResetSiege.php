<?php
/** @noinspection GrazieInspection */
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Event;

use Lemuria\Engine\Fantasya\Action;
use Lemuria\Engine\Fantasya\Effect\SiegeEffect;
use Lemuria\Engine\Fantasya\State;
use Lemuria\Lemuria;
use Lemuria\Model\Catalog;
use Lemuria\Model\Fantasya\Construction;

/**
 * Reset siege effects.
 */
final class ResetSiege extends AbstractEvent
{
	public function __construct(State $state) {
		parent::__construct($state, Action::MIDDLE);
	}

	protected function run(): void {
		$siege = new SiegeEffect($this->state);
		foreach (Lemuria::Catalog()->getAll(Catalog::CONSTRUCTIONS) as $construction /* @var Construction $construction */) {
			$effect = Lemuria::Score()->find($siege->setConstruction($construction));
			if ($effect instanceof SiegeEffect) {
				$effect->reset();
			}
		}
	}
}
