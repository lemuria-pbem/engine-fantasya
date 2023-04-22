<?php
declare (strict_types = 1);
namespace Lemuria\Engine\Fantasya\Command;

use Lemuria\Engine\Fantasya\Factory\ReassignTrait;
use Lemuria\Engine\Fantasya\Factory\UnicumTrait;
use Lemuria\Engine\Fantasya\Message\Unit\TakeNoCompositionMessage;
use Lemuria\Engine\Fantasya\Message\Unit\TakeNoUnicumMessage;
use Lemuria\Engine\Fantasya\Message\Unit\TakeUnsupportedMessage;
use Lemuria\Engine\Fantasya\Phrase;
use Lemuria\Model\Domain;
use Lemuria\Model\Fantasya\Practice;
use Lemuria\Model\Reassignment;

/**
 * This command is used to take an Unicum into possession.
 *
 * - NEHMEN <Unicum>
 * - NEHMEN <composition> <Unicum>
 */
final class Take extends UnitCommand implements Operator, Reassignment
{
	use ReassignTrait;
	use UnicumTrait;

	protected function run(): void {
		$id = $this->findUnicum();
		if (!$this->unicum) {
			$this->message(TakeNoUnicumMessage::class)->p($id);
			return;
		}
		$composition = $this->unicum->Composition();
		if ($composition !== $this->composition) {
			$this->message(TakeNoCompositionMessage::class)->s($this->composition)->p($id);
			return;
		}
		if ($composition->supports(Practice::Take)) {
			$this->getOperate(Practice::Take)->take();
		} else {
			$this->message(TakeUnsupportedMessage::class)->e($this->unicum)->s($this->unicum->Composition());
		}
	}

	protected function checkReassignmentDomain(Domain $domain): bool {
		return $domain === Domain::Unicum;
	}

	protected function getReassignPhrase(string $old, string $new): ?Phrase {
		return $this->getReassignPhraseForParameter($this->argumentIndex - 1, $old, $new);
	}
}
