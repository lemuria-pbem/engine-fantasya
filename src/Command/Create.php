<?php
declare (strict_types = 1);
namespace Lemuria\Engine\Fantasya\Command;

use function Lemuria\isInt;
use Lemuria\Engine\Fantasya\Command;
use Lemuria\Engine\Fantasya\Command\Create\Griffinegg;
use Lemuria\Engine\Fantasya\Command\Create\RawMaterial\Herb;
use Lemuria\Engine\Fantasya\Command\Create\Resource;
use Lemuria\Engine\Fantasya\Command\Create\Road;
use Lemuria\Engine\Fantasya\Command\Create\Temp;
use Lemuria\Engine\Fantasya\Command\Create\Unknown;
use Lemuria\Engine\Fantasya\Exception\InvalidCommandException;
use Lemuria\Engine\Fantasya\Exception\UnknownItemException;
use Lemuria\Engine\Fantasya\Factory\Model\Herb as HerbModel;
use Lemuria\Engine\Fantasya\Factory\Model\Job;
use Lemuria\Model\Fantasya\Commodity\Griffinegg as GriffineggModel;
use Lemuria\Model\Fantasya\Herb as HerbInterface;

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
 * - MACHEN Greifenei|Greifeneier
 * - MACHEN <amount> Greifenei|Greifeneier
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

		// MACHEN <amount> <Ressource>
		if (isInt($param)) {
			$what   = $this->phrase->getLine(2);
			$number = (int)$param;
		} else {
			$what  = $param;
			$param = $this->phrase->getParameter(2);
			if (isInt($param)) {
				$number = (int)$param;
			} else {
				$what   = $this->phrase->getLine();
				$number = 0;
			}
		}

		// MACHEN Kräuter
		$lower = strtolower($what);
		if ($lower === 'kraut' || $lower === 'kraeuter' || $lower === 'kräuter') {
			return new Herb($this->phrase, $this->context, new Job(new HerbModel(), $number));
		}
		if ($lower === 'greifenei' || $lower === 'greifeneier') {
			$egg = self::createCommodity(GriffineggModel::class);
			return new Griffinegg($this->phrase, $this->context, new Job($egg, $number));
		}

		try {
			$resource = $this->context->Factory()->resource($what);
			$job      = new Job($resource, $number);
			if ($resource instanceof HerbInterface) {
				return new Herb($this->phrase, $this->context, $job);
			}
			return new Resource($this->phrase, $this->context, $job);
		} catch (UnknownItemException $e) {
			return new Unknown($this->phrase, $this->context, $e);
		}
	}
}
