<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Event\Administrator;

use Lemuria\Engine\Fantasya\Event\AbstractEvent;
use Lemuria\Engine\Fantasya\Message\Party\Administrator\OvercrowdedMessage;
use Lemuria\Engine\Fantasya\Priority;
use Lemuria\Engine\Fantasya\State;
use Lemuria\Lemuria;
use Lemuria\Model\Fantasya\Construction;
use Lemuria\Model\Fantasya\Talent\Constructing;

/**
 * This event searches for overcrowded constructions.
 */
final class Overcrowded extends AbstractEvent
{
	public function __construct(State $state) {
		parent::__construct($state, Priority::After);
	}

	protected function run(): void {
		foreach (Construction::all() as $construction) {
			if ($construction->getFreeSpace() > 0) {
				continue;
			}
			$space = $construction->Size() - $construction->Inhabitants()->Size();
			if ($space < 0) {
				if (!$this->isInConstruction($construction)) {
					$party  = $construction->Inhabitants()->Owner()->Party();
					$name   = (string)$construction;
					$region = (string)$construction->Region();
					$this->message(OvercrowdedMessage::class, $party)->p($name)->p($region, OvercrowdedMessage::REGION);
					Lemuria::Log()->critical('Construction ' . $construction . ' in ' . $construction->Region() . ' is overcrowded with ' . -$space . ' persons.');
				} else {
					Lemuria::Log()->critical($construction . ' (under construction) in ' . $construction->Region() . ' is overcrowded with ' . -$space . ' persons.');
				}
			}
		}
	}

	private function isInConstruction(Construction $construction): bool {
		$inhabitants = $construction->Inhabitants();
		if ($inhabitants->count() === 1) {
			$knowledge = $inhabitants->Owner()->Knowledge();
			if (isset($knowledge[Constructing::class])) {
				return true;
			}
		}
		return false;
	}
}
