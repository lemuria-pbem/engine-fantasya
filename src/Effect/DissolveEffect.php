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
		parent::__construct($state, Priority::Middle);
	}

	protected function run(): void {
		$unit = $this->Unit();
		Lemuria::Catalog()->reassign($unit);
		$unit->Region()->Residents()->remove($unit);
		$unit->Party()->People()->remove($unit);
		Lemuria::Catalog()->remove($unit);
		Lemuria::Score()->remove($this);
		$this->message(VanishEffectMessage::class, $unit);
	}
}
