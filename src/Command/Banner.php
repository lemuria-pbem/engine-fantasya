<?php
declare (strict_types = 1);
namespace Lemuria\Engine\Fantasya\Command;

use Lemuria\Engine\Fantasya\Message\Party\BannerMessage;

/**
 * Sets the party banner.
 *
 * - BANNER <banner text>
 */
final class Banner extends UnitCommand
{
	protected function run(): void {
		$party = $this->unit->Party();
		$party->setBanner($this->phrase->getLine());
		$this->message(BannerMessage::class, $party);
	}

	protected function checkSize(): bool {
		return true;
	}
}
