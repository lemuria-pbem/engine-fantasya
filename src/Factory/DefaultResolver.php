<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Factory;

use Lemuria\Engine\Fantasya\Activity;
use Lemuria\Engine\Fantasya\Command;

class DefaultResolver
{
	/**
	 * @param array<Command> $defaults
	 */
	public function __construct(protected array $defaults) {
	}

	/**
	 * @return array<Command>
	 */
	public function resolve(): array {
		$first = 0;
		$n     = count($this->defaults);
		while ($first < $n) {
			if (!isset($this->defaults[$first])) {
				$first++;
				continue;
			}
			$command = $this->defaults[$first];
			$phrase  = (string)$command;
			$second  = 0;
			while ($second < $n) {
				if ($second === $first || !isset($this->defaults[$second])) {
					$second++;
					continue;
				}
				if ($command instanceof Activity) {
					$other = $this->defaults[$second];
					if ($other instanceof Activity) {
						if ((string)$other === $phrase || !$command->allows($other)) {
							unset($this->defaults[$second]);
							$n--;
							continue;
						}
					}
				}
				$second++;
			}
			$first++;
		}
		return $this->defaults;
	}
}
