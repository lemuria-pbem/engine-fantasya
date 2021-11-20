<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Event\Administrator;

use Lemuria\Engine\Fantasya\Action;
use Lemuria\Engine\Fantasya\Event\AbstractEvent;
use Lemuria\Engine\Fantasya\Message\Party\Administrator\OvercrowdedMessage;
use Lemuria\Engine\Fantasya\State;
use Lemuria\Lemuria;
use Lemuria\Model\Catalog;
use Lemuria\Model\Fantasya\Construction;

/**
 * This event searches for overcrowded constructions.
 */
final class Overcrowded extends AbstractEvent
{
	public function __construct(State $state) {
		parent::__construct($state, Action::AFTER);
	}

	protected function run(): void {
		foreach (Lemuria::Catalog()->getAll(Catalog::CONSTRUCTIONS) as $construction /* @var Construction $construction */) {
			if ($construction->getFreeSpace() > 0) {
				continue;
			}
			$space = $construction->Size() - $construction->Inhabitants()->Size();
			if ($space < 0) {
				$party  = $construction->Inhabitants()->Owner()->Party();
				$name   = (string)$construction;
				$region = (string)$construction->Region();
				$this->message(OvercrowdedMessage::class, $party)->p($name)->p($region, OvercrowdedMessage::REGION);
				Lemuria::Log()->critical('Construction ' . $construction . ' in ' . $construction->Region() . ' is overcrowded with ' . -$space . ' persons.');
			}
		}
	}
}
