<?php
declare (strict_types = 1);
namespace Lemuria\Engine\Lemuria\Command;

use function Lemuria\isInt;

use Lemuria\Engine\Lemuria\Command;
use Lemuria\Engine\Lemuria\Command\Create\Resource;
use Lemuria\Engine\Lemuria\Command\Create\Temp;
use Lemuria\Engine\Lemuria\Exception\InvalidCommandException;
use Lemuria\Engine\Lemuria\Factory\Model\Job;

/**
 * Implementation of command MACHEN.
 *
 * The command determines the create sub command and delegates to it.
 *
 * - MACHEN <Resource>
 * - MACHEN <Resource> <size>
 * - MACHEN <amount> <Resource>
 * - MACHEN Temp
 * - MACHEN Temp <id>
 */
final class Create extends DelegatedCommand
{
	protected function createDelegate(): Command {
		if (count($this->phrase) > 2) {
			throw new InvalidCommandException($this);
		}

		$param = $this->phrase->getParameter();
		// MACHEN TEMP
		if (strtoupper($param) === 'TEMP') {
			return new Temp($this->phrase, $this->context);
		}

		// MACHEN <amount> <Ressource>
		if (isInt($param)) {
			$what   = $this->phrase->getParameter(2);
			$number = (int)$param;
		} else {
			$what   = $param;
			$number = (int)$this->phrase->getParameter(2);
		}
		$resource = $this->context->Factory()->resource($what);
		return new Resource($this->phrase, $this->context, new Job($resource, $number));
	}
}
