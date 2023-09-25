<?php
declare (strict_types = 1);
namespace Lemuria\Engine\Fantasya\Command;

use function Lemuria\isInt;
use function Lemuria\isPercentage;
use Lemuria\Engine\Fantasya\Command;
use Lemuria\Engine\Fantasya\Command\Create\Construction;
use Lemuria\Engine\Fantasya\Command\Create\Griffinegg;
use Lemuria\Engine\Fantasya\Command\Create\Herb;
use Lemuria\Engine\Fantasya\Command\Create\Resource;
use Lemuria\Engine\Fantasya\Command\Create\Road;
use Lemuria\Engine\Fantasya\Command\Create\Temp;
use Lemuria\Engine\Fantasya\Command\Create\Unicum;
use Lemuria\Engine\Fantasya\Command\Create\Unknown;
use Lemuria\Engine\Fantasya\Command\Create\Vessel;
use Lemuria\Engine\Fantasya\Exception\InvalidCommandException;
use Lemuria\Engine\Fantasya\Exception\UnknownItemException;
use Lemuria\Engine\Fantasya\Factory\Model\AnyBuilding;
use Lemuria\Engine\Fantasya\Factory\Model\AnyShip;
use Lemuria\Engine\Fantasya\Factory\Model\Herb as HerbModel;
use Lemuria\Engine\Fantasya\Factory\Model\Job;
use Lemuria\Model\Fantasya\Commodity\Griffinegg as GriffineggModel;
use Lemuria\Model\Fantasya\Herb as HerbInterface;

/**
 * Implementation of command MACHEN.
 *
 * The command determines the create sub command and delegates to it.
 *
 * - MACHEN Gebäude <ID> (from outside)
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
 * - MACHEN <Unicum> [<ID>]
 */
final class Create extends DelegatedCommand
{
	protected function createDelegate(): Command {
		$n = count($this->phrase);
		if ($n > 3) {
			throw new InvalidCommandException($this);
		}

		$param = $this->phrase->getParameter();
		$lower = mb_strtolower($param);
		// MACHEN TEMP
		if ($lower === 'temp') {
			return new Temp($this->phrase, $this->context);
		}
		// MACHEN Straße
		if ($lower === 'straße' || $lower === 'strasse') {
			return new Road($this->phrase, $this->context);
		}
		// MACHEN Schiff <size>
		if ($n <= 2 && $lower === 'schiff') {
			$size = $n === 2 ? max(0, (int)$this->phrase->getParameter(2)) : 0;
			return new Vessel($this->phrase, $this->context, new Job(new AnyShip(), $size));
		}
		// MACHEN Gebäude <size> | MACHEN Gebäude <ID> <size>
		if ($lower === 'gebäude' || $lower === 'gebaeude') {
			$size = $n >= 2 ? max(0, (int)$this->phrase->getParameter(0)) : 0;
			return new Construction($this->phrase, $this->context, new Job(new AnyBuilding(), $size));
		}

		// MACHEN <amount> <Ressource>
		$threshold = null;
		if (in_array($param, ['-0', '-0%'])) {
			$number    = 0;
			$threshold = 0;
			$what      = $this->phrase->getLine(2);
		} elseif (isInt($param)) {
			$number = (int)$param;
			if ($number < 0) {
				$threshold = abs($number);
				$number    = 0;
			}
			$what = $this->phrase->getLine(2);
		} elseif (isPercentage($param)) {
			$percentage = (int)substr($param, 0, strlen($param) - 1);
			if ($percentage < 0) {
				$threshold = abs($percentage) / 100;
			}
			$number = 0;
			$what   = $this->phrase->getLine(2);
		} else {
			$what  = $param;
			$param = $this->phrase->getParameter(2);
			if (isInt($param)) {
				$number = max(0, (int)$param);
			} else {
				$what   = $this->phrase->getLine();
				$number = 0;
			}
		}

		// MACHEN Kräuter
		$lower = mb_strtolower($what);
		if ($lower === 'kraut' || $lower === 'kraeuter' || $lower === 'kräuter') {
			return new Herb($this->phrase, $this->context, new Job(new HerbModel(), $number, $threshold));
		}
		if ($lower === 'greifenei' || $lower === 'greifeneier') {
			$egg = self::createCommodity(GriffineggModel::class);
			return new Griffinegg($this->phrase, $this->context, new Job($egg, $number, $threshold));
		}

		// MACHEN <Unicum> [<ID>]
		if ($this->context->Factory()->isComposition($what)) {
			return new Unicum($this->phrase, $this->context);
		}

		try {
			$resource = $this->context->Factory()->resource($what);
			$job      = new Job($resource, $number, $threshold);
			if ($resource instanceof HerbInterface) {
				return new Herb($this->phrase, $this->context, $job);
			}
			return new Resource($this->phrase, $this->context, $job);
		} catch (UnknownItemException $e) {
			return new Unknown($this->phrase, $this->context, $e);
		}
	}
}
