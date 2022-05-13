<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Effect;

use Lemuria\Engine\Fantasya\Message\Unit\VanishEffectMessage;
use Lemuria\Engine\Fantasya\Priority;
use Lemuria\Engine\Fantasya\State;
use Lemuria\Lemuria;

final class DissolveEffect extends AbstractUnitEffect
{
	public function __construct(State $state) {
		parent::__construct($state, Priority::MIDDLE);
	}

	protected function run(): void {
		$unit = $this->Unit();
		$unit->Region()->Residents()->remove($unit);
		$unit->Party()->People()->remove($unit);
		Lemuria::Catalog()->reassign($unit);
		Lemuria::Catalog()->remove($unit);
		Lemuria::Score()->remove($this);
		$this->message(VanishEffectMessage::class, $unit);
	}
}
