<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Command\Template;

use Lemuria\Engine\Fantasya\Command\UnitCommand;
use Lemuria\Engine\Fantasya\Phrase;

abstract class AbstractTemplate extends UnitCommand
{
	protected function checkSize(): bool {
		return true;
	}

	protected function cleanLine(Phrase $phrase, int $from = 1): string {
		return trim($phrase->getLine($from), "'\"");
	}

	protected function replaceTempUnits(string $line, int $i = 0): string {
		$mapper = $this->context->UnitMapper();
		$parts  = explode(' ', $line);
		$n      = count($parts) - 1;
		for (; $i < $n; $i++) {
			if (strtoupper($parts[$i]) === 'TEMP') {
				$id = strtolower($parts[$i + 1]);
				if ($mapper->has($id)) {
					$id = (string)$mapper->get($id)->getUnit()->Id();
					unset($parts[$i]);
					$parts[++$i] = $id;
				}
			}
		}
		return implode(' ', $parts);
	}
}
