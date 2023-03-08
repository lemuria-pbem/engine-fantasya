<?php
declare (strict_types = 1);
namespace Lemuria\Engine\Fantasya\Command;

use Lemuria\Engine\Fantasya\Factory\ReassignTrait;
use Lemuria\Engine\Fantasya\Factory\UnicumTrait;
use Lemuria\Engine\Fantasya\Message\Unit\DevastateNoCompositionMessage;
use Lemuria\Engine\Fantasya\Message\Unit\DevastateNoUnicumMessage;
use Lemuria\Engine\Fantasya\Message\Unit\DevastateUnsupportedMessage;
use Lemuria\Engine\Fantasya\Phrase;
use Lemuria\Model\Domain;
use Lemuria\Model\Fantasya\Practice;
use Lemuria\Model\Reassignment;

/**
 * This command is used to destroy an Unicum.
 *
 * - VERNICHTEN <Unicum>
 * - VERNICHTEN <composition> <Unicum>
 */
final class Devastate extends UnitCommand implements Operator, Reassignment
{
	use ReassignTrait;
	use UnicumTrait;

	protected function run(): void {
		$id = $this->parseUnicum();
		if (!$this->unicum) {
			$this->message(DevastateNoUnicumMessage::class)->p($id);
			return;
		}
		$composition = $this->unicum->Composition();
		if ($composition !== $this->composition) {
			$this->message(DevastateNoCompositionMessage::class)->s($this->composition)->p($id);
			return;
		}
		if ($composition->supports(Practice::Destroy)) {
			$this->getOperate(Practice::Destroy)->destroy();
		} else {
			$this->message(DevastateUnsupportedMessage::class)->e($this->unicum)->s($this->unicum->Composition());
		}
	}

	protected function checkReassignmentDomain(Domain $domain): bool {
		return $domain === Domain::Unicum;
	}

	protected function getReassignPhrase(string $old, string $new): ?Phrase {
		return $this->unicum ? $this->getReassignPhraseForParameter($this->phrase->count(), $old, $new) : null;
	}
}
