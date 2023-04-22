<?php
declare (strict_types = 1);
namespace Lemuria\Engine\Fantasya\Command;

use Lemuria\Engine\Fantasya\Factory\ReassignTrait;
use Lemuria\Engine\Fantasya\Factory\UnicumTrait;
use Lemuria\Engine\Fantasya\Message\Unit\ReadNoCompositionMessage;
use Lemuria\Engine\Fantasya\Message\Unit\ReadNoUnicumMessage;
use Lemuria\Engine\Fantasya\Message\Unit\ReadUnsupportedMessage;
use Lemuria\Engine\Fantasya\Phrase;
use Lemuria\Model\Domain;
use Lemuria\Model\Fantasya\Practice;
use Lemuria\Model\Reassignment;

/**
 * This command is used to ask for information about an Unicum.
 *
 * - LESEN <Unicum>
 * - LESEN <composition> <Unicum>
 * - UNTERSUCHEN <Unicum>
 * - UNTERSUCHEN <composition> <Unicum>
 */
final class Read extends UnitCommand implements Operator, Reassignment
{
	use ReassignTrait;
	use UnicumTrait;

	protected function run(): void {
		$id = $this->parseUnicum();
		if (!$this->unicum) {
			$this->message(ReadNoUnicumMessage::class)->p($id);
			return;
		}
		$composition = $this->unicum->Composition();
		if ($composition !== $this->composition) {
			$this->message(ReadNoCompositionMessage::class)->s($this->composition)->p($id);
			return;
		}
		if ($composition->supports(Practice::Read)) {
			$this->getOperate(Practice::Read)->read();
		} else {
			$this->message(ReadUnsupportedMessage::class)->e($this->unicum)->s($this->unicum->Composition());
		}
	}

	protected function checkReassignmentDomain(Domain $domain): bool {
		return $domain === Domain::Unicum;
	}

	protected function getReassignPhrase(string $old, string $new): ?Phrase {
		if ((string)$this->unicum->Id() === $old) {
			return $this->getReassignPhraseForParameter($this->argumentIndex - 1, $old, $new);
		}
		return null;
	}
}
