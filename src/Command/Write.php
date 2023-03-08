<?php
declare (strict_types = 1);
namespace Lemuria\Engine\Fantasya\Command;

use Lemuria\Engine\Fantasya\Activity;
use Lemuria\Engine\Fantasya\Factory\OperatorActivityTrait;
use Lemuria\Engine\Fantasya\Factory\ReassignTrait;
use Lemuria\Engine\Fantasya\Factory\UnicumTrait;
use Lemuria\Engine\Fantasya\Message\Unit\WriteNoCompositionMessage;
use Lemuria\Engine\Fantasya\Message\Unit\WriteNoUnicumMessage;
use Lemuria\Engine\Fantasya\Message\Unit\WriteUnsupportedMessage;
use Lemuria\Engine\Fantasya\Phrase;
use Lemuria\Model\Domain;
use Lemuria\Model\Fantasya\Composition;
use Lemuria\Model\Fantasya\Practice;
use Lemuria\Model\Reassignment;

/**
 * This command is used to write to an Unicum.
 *
 * - SCHREIBEN <Unicum> ...
 * - SCHREIBEN <composition> <Unicum> ...
 */
final class Write extends UnitCommand implements Activity, Operator, Reassignment
{
	use OperatorActivityTrait;
	use ReassignTrait;
	use UnicumTrait;

	protected bool $preventDefault = true;

	public function Composition(): Composition {
		$this->parseUnicum();
		return $this->unicum->Composition();
	}

	protected function run(): void {
		$this->parseUnicumWithArguments();
		if (!$this->unicum) {
			$this->message(WriteNoUnicumMessage::class);
			return;
		}
		$composition = $this->unicum->Composition();
		if ($composition !== $this->composition) {
			$this->message(WriteNoCompositionMessage::class)->s($this->composition)->p((string)$this->unicum->Id());
			return;
		}
		if ($composition->supports(Practice::Write)) {
			$this->getOperate(Practice::Write)->write();
		} else {
			$this->message(WriteUnsupportedMessage::class)->e($this->unicum)->s($this->unicum->Composition());
		}
	}

	protected function checkReassignmentDomain(Domain $domain): bool {
		return $domain === Domain::Unicum;
	}

	protected function getReassignPhrase(string $old, string $new): ?Phrase {
		return $this->getReassignPhraseForParameter($this->argumentIndex - 1, $old, $new);
	}
}
