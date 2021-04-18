<?php
declare (strict_types = 1);
namespace Lemuria\Engine\Fantasya\Command;

use function Lemuria\isInt;
use Lemuria\Engine\Fantasya\Command;
use Lemuria\Engine\Fantasya\Command\Create\Resource;
use Lemuria\Engine\Fantasya\Command\Create\Road;
use Lemuria\Engine\Fantasya\Command\Create\Temp;
use Lemuria\Engine\Fantasya\Command\Create\Unknown;
use Lemuria\Engine\Fantasya\Exception\InvalidCommandException;
use Lemuria\Engine\Fantasya\Exception\UnknownItemException;
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
 * - MACHEN Straße|Strasse <direction> [<amount>]
 */
final class Create extends DelegatedCommand
{
	protected function createDelegate(): Command {
		if (count($this->phrase) > 3) {
			throw new InvalidCommandException($this);
		}

		$param = $this->phrase->getParameter();
		$upper = strtolower($param);
		// MACHEN TEMP
		if ($upper === 'temp') {
			return new Temp($this->phrase, $this->context);
		}
		if ($upper === 'straße' || $upper === 'strasse') {
			return new Road($this->phrase, $this->context);
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
