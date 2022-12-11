<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Effect;

use function Lemuria\randInt;
use Lemuria\Engine\Fantasya\Message\Vessel\ExcessCargoMessage;
use Lemuria\Engine\Fantasya\Priority;
use Lemuria\Engine\Fantasya\State;

final class ExcessCargo extends AbstractVesselEffect
{
	private const MIN_DAMAGE = 5;

	private const MAX_DAMAGE = 10;

	public function __construct(State $state) {
		parent::__construct($state, Priority::After);
	}

	protected function run(): void {
		$vessel = $this->Vessel();
		$damage = randInt(self::MIN_DAMAGE, self::MAX_DAMAGE) / 100;
		$vessel->setCompletion(max(0, $vessel->Completion() - $damage));
		$this->message(ExcessCargoMessage::class, $vessel);
	}
}
