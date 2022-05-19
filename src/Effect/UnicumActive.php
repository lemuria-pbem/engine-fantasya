<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Effect;

use Lemuria\Engine\Fantasya\Factory\MessageTrait;
use Lemuria\Engine\Fantasya\Message\Unit\Operate\MagicRingActiveMessage;
use Lemuria\Engine\Fantasya\Priority;
use Lemuria\Engine\Fantasya\State;
use Lemuria\Lemuria;
use Lemuria\Model\Fantasya\MagicRing;
use Lemuria\Model\Fantasya\Unit;

final class UnicumActive extends AbstractUnicumEffect
{
	use MessageTrait;

	public function __construct(State $state) {
		parent::__construct($state, Priority::AFTER);
	}

	protected function run(): void {
		$unicum      = $this->Unicum();
		$composition = $unicum->Composition();
		if ($composition instanceof MagicRing) {
			$unit = $unicum->Collector();
			if ($unit instanceof Unit) {
				$this->message(MagicRingActiveMessage::class, $unit)->e($unicum)->s($composition);
			} else {
				Lemuria::Score()->remove($this);
				Lemuria::Log()->debug('The magic ring ' . $unicum . ' is not active anymore.');
			}
		}
	}
}
