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
use Lemuria\Engine\Fantasya\Factory\Model\Herb;
use Lemuria\Engine\Fantasya\Factory\Model\Job;
use Lemuria\Model\Fantasya\Herb as HerbInterface;
use Lemuria\Singleton;

/**
 * Implementation of command MACHEN.
 *
 * The command determines the create sub command and delegates to it.
 *
 * - MACHEN <Resource>
 * - MACHEN <Resource> <size>
 * - MACHEN <amount> <Resource>
 * - MACHEN Kraut|Kraeuter|Kräuter
 * - MACHEN <amount> Kraut|Kraeuter|Kräuter
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
		$lower = strtolower($param);
		// MACHEN TEMP
		if ($lower === 'temp') {
			return new Temp($this->phrase, $this->context);
		}
		// MACHEN Straße
		if ($lower === 'straße' || $lower === 'strasse') {
			return new Road($this->phrase, $this->context);
		}

		// MACHEN <amount> Kräuter
		// MACHEN <amount> <Ressource>
		if (isInt($param)) {
			$what   = $this->phrase->getParameter(2);
			$number = (int)$param;
		} else {
			$what   = $param;
			$number = (int)$this->phrase->getParameter(2);
		}
		try {
			$resource = $this->getResource($what);
			return new Resource($this->phrase, $this->context, new Job($resource, $number));
		} catch (UnknownItemException $e) {
			return new Unknown($this->phrase, $this->context, $e);
		}
	}

	private function getResource(string $class): Singleton {
		$resource = $this->context->Factory()->resource($class);

		// If concrete herb is created, replace it with Herb delegate.
		if ($resource instanceof HerbInterface) {
			if ($resource::class !== Herb::class) {
				if ($resource === $this->unit->Region()->Herbage()?->Herb()) {
					$resource = $this->context->Factory()->resource('kraut');
				}
			}
		}

		return $resource;
	}
}
