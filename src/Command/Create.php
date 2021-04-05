<?php
declare (strict_types = 1);
namespace Lemuria\Engine\Fantasya\Command;

use Lemuria\Engine\Fantasya\Exception\UnknownItemException;
use function Lemuria\isInt;

use Lemuria\Engine\Fantasya\Command;
use Lemuria\Engine\Fantasya\Command\Create\Resource;
use Lemuria\Engine\Fantasya\Command\Create\Temp;
use Lemuria\Engine\Fantasya\Command\Create\Unknown;
use Lemuria\Engine\Fantasya\Exception\InvalidCommandException;
use Lemuria\Engine\Fantasya\Factory\Model\Job;

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
		try {
			$resource = $this->context->Factory()->resource($what);
			return new Resource($this->phrase, $this->context, new Job($resource, $number));
		} catch (UnknownItemException $e) {
			return new Unknown($this->phrase, $this->context, $e);
		}
	}
}
