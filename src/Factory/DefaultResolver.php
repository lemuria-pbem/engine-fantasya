<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Factory;

use Lemuria\Engine\Fantasya\Activity;
use Lemuria\Engine\Fantasya\Command;

class DefaultResolver
{
	/**
	 * @param Command[] $defaults
	 */
	public function __construct(protected array $defaults) {
	}

	/**
	 * @return Command[]
	 */
	public function resolve(): array {
		$first = 0;
		$n     = count($this->defaults);
		while ($first < $n) {
			$command = $this->defaults[$first];
			$second  = 0;
			while ($second < $n) {
				if ($second === $first) {
					$second++;
					continue;
				}
				if ($command instanceof Activity) {
					$other = $this->defaults[$second];
					if ($other instanceof Activity) {
						if (!$command->allows($other)) {
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
