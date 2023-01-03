<?php
declare (strict_types = 1);
namespace Lemuria\Engine\Fantasya\Command;

use Lemuria\Engine\Fantasya\Message\Unit\CancelMessage;
use Lemuria\Engine\Fantasya\Message\Unit\CancelNoneMessage;
use Lemuria\Id;
use Lemuria\Lemuria;

/**
 * Cancel one or all trades.
 *
 * - BEENDEN
 * - BEENDEN <trade> [<trade>...]
 */
final class Cancel extends UnitCommand
{
	protected function run(): void {
		$trades = $this->unit->Trades();
		$cancel = [];

		$n = $this->phrase->count();
		if ($n > 0) {
			for ($i = 1; $i <= $n; $i++) {
				$cancel[] = $this->phrase->getParameter($i);
			}
		} else {
			foreach ($trades as $trade) {
				$cancel[] = (string)$trade->Id();
			}
		}

		$catalog = Lemuria::Catalog();
		foreach ($cancel as $id) {
			$id = $this->toId($id);
			if ($trades->has($id)) {
				$trade = $trades[$id];
				$trades->offsetUnset($id);
				$catalog->remove($trade);
				$this->message(CancelMessage::class)->p((string)$id);
			} else {
				$this->message(CancelNoneMessage::class)->p((string)$id);
			}
		}
	}
}
