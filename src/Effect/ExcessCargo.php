<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Lemuria\Effect;

use JetBrains\PhpStorm\Pure;

use Lemuria\Engine\Lemuria\Action;
use Lemuria\Engine\Lemuria\Message\Vessel\ExcessCargoMessage;
use Lemuria\Engine\Lemuria\State;

final class ExcessCargo extends AbstractVesselEffect
{
	private const MIN_DAMAGE = 5;

	private const MAX_DAMAGE = 10;

	#[Pure] public function __construct(State $state) {
		parent::__construct($state, Action::AFTER);
	}

	protected function run(): void {
		$vessel = $this->Vessel();
		$damage = rand(self::MIN_DAMAGE, self::MAX_DAMAGE) / 100;
		$vessel->setCompletion(max(0, $vessel->Completion() - $damage));
		$this->message(ExcessCargoMessage::class, $vessel);
	}
}
