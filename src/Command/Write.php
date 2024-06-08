<?php
declare (strict_types = 1);
namespace Lemuria\Engine\Fantasya\Command;

use Lemuria\Engine\Fantasya\Activity;
use Lemuria\Engine\Fantasya\Context;
use Lemuria\Engine\Fantasya\Factory\OperatorActivityTrait;
use Lemuria\Engine\Fantasya\Factory\ReassignTrait;
use Lemuria\Engine\Fantasya\Factory\UnicumTrait;
use Lemuria\Engine\Fantasya\Factory\WorkloadTrait;
use Lemuria\Engine\Fantasya\Message\Unit\WriteNoCompositionMessage;
use Lemuria\Engine\Fantasya\Message\Unit\WriteNoneMessage;
use Lemuria\Engine\Fantasya\Message\Unit\WriteNoUnicumMessage;
use Lemuria\Engine\Fantasya\Message\Unit\WriteUnsupportedMessage;
use Lemuria\Engine\Fantasya\Phrase;
use Lemuria\Model\Domain;
use Lemuria\Model\Fantasya\Composition;
use Lemuria\Model\Fantasya\Practice;
use Lemuria\Model\Fantasya\Talent\Magic;
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
	use WorkloadTrait;

	protected bool $preventDefault = true;

	public function __construct(Phrase $phrase, Context $context) {
		parent::__construct($phrase, $context);
		$this->initWorkload();
	}

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
			$craft      = $composition->getCraft();
			$production = (int)floor($this->calculus()->knowledge($craft->Talent())->Level() / $craft->Level());
			$production = $this->reduceByWorkload($production);
			if ($production > 0) {
				$this->getOperate(Practice::Write)->write();
				$this->addToWorkload(1);
			} else {
				$this->message(WriteNoneMessage::class)->e($this->unicum)->s($this->composition);
			}
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
