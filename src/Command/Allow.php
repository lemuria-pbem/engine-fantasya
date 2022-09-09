<?php
declare (strict_types = 1);
namespace Lemuria\Engine\Fantasya\Command;

use Lemuria\Engine\Fantasya\Message\Unit\AllowAllMessage;
use Lemuria\Engine\Fantasya\Message\Unit\AllowCommodityMessage;
use Lemuria\Engine\Fantasya\Message\Unit\AllowKindMessage;
use Lemuria\Engine\Fantasya\Message\Unit\ForbidAllMessage;
use Lemuria\Model\Fantasya\Commodity;
use Lemuria\Model\Fantasya\Container;

/**
 * Allow tradeables.
 * - ERLAUBEN [Alles]
 * - ERLAUBEN Nichts
 * - ERLAUBEN <commodity>...
 * - ERLAUBEN <kind>...
 */
final class Allow extends RegulateCommand
{
	protected function run(): void {
		parent::run();
		if ($this->market) {
			if ($this->what === RegulateCommand::NONE) {
				if ($this->isChange(false)) {
					$this->message(ForbidAllMessage::class);
				}
			} elseif ($this->what === RegulateCommand::ALL) {
				if ($this->isChange(true)) {
					$this->message(AllowAllMessage::class);
				}
			} else {
				foreach ($this->commodities as $commodity /* @var Commodity $commodity */) {
					$this->tradeables->allow($commodity);
					if ($commodity instanceof Container) {
						$this->message(AllowKindMessage::class)->s($commodity);
					} else {
						$this->message(AllowCommodityMessage::class)->s($commodity);
					}
				}
			}
		}
	}
}
