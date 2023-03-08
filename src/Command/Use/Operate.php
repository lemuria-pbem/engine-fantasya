<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Command\Use;

use Lemuria\Engine\Fantasya\Activity;
use Lemuria\Engine\Fantasya\Command\Operator;
use Lemuria\Engine\Fantasya\Command\UnitCommand;
use Lemuria\Engine\Fantasya\Factory\DefaultActivityTrait;
use Lemuria\Engine\Fantasya\Factory\OperateTrait;
use Lemuria\Engine\Fantasya\Factory\ReassignTrait;
use Lemuria\Engine\Fantasya\Phrase;
use Lemuria\Model\Domain;
use Lemuria\Model\Fantasya\Practice;
use Lemuria\Model\Reassignment;

/**
 * Use an unicum.
 *
 * - BENUTZEN <Unicum>
 * - BENUTZEN <composition> <Unicum>
 */
final class Operate extends UnitCommand implements Activity, Operator, Reassignment
{
	use DefaultActivityTrait;
	use OperateTrait;
	use ReassignTrait;

	protected bool $preventDefault = true;

	protected function run(): void {
		$this->parseOperate(Practice::Apply)?->apply();
	}

	protected function checkReassignmentDomain(Domain $domain): bool {
		return $domain === Domain::Unicum;
	}

	protected function getReassignPhrase(string $old, string $new): ?Phrase {
		$operate = $this->parseOperate(Practice::Apply);
		if ($operate && (string)$this->unicum->Id() === $old) {
			return $this->getReassignPhraseForParameter($this->argumentIndex - 1, $old, $new);
		}
		return null;
	}
}
