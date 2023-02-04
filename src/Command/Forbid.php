<?php
declare (strict_types = 1);
namespace Lemuria\Engine\Fantasya\Command;

use Lemuria\Engine\Fantasya\Message\Unit\AllowAllMessage;
use Lemuria\Engine\Fantasya\Message\Unit\ForbidAllMessage;
use Lemuria\Engine\Fantasya\Message\Unit\ForbidCommodityMessage;
use Lemuria\Engine\Fantasya\Message\Unit\ForbidKindMessage;
use Lemuria\Model\Fantasya\Commodity;
use Lemuria\Model\Fantasya\Container;

/**
 * Forbid tradeables.
 *
 * - VERBIETEN [Alles]
 * - VERBIETEN Nichts
 * - VERBIETEN <commodity>...
 * - VERBIETEN <kind>...
 */
final class Forbid extends RegulateCommand
{
	protected function run(): void {
		parent::run();
		if ($this->market) {
			if ($this->what === RegulateCommand::NONE) {
				if ($this->isChange(true)) {
					$this->message(AllowAllMessage::class);
				}
			} elseif ($this->what === RegulateCommand::ALL) {
				if ($this->isChange(false)) {
					$this->message(ForbidAllMessage::class);
				}
			} else {
				foreach ($this->commodities as $commodity /** @var Commodity $commodity */) {
					$this->tradeables->ban($commodity);
					if ($commodity instanceof Container) {
						$this->message(ForbidKindMessage::class)->s($commodity);
					} else {
						$this->message(ForbidCommodityMessage::class)->s($commodity);
					}
				}
			}
		}
	}
}
