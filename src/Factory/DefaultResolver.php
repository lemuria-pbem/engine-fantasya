<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Factory;

use Lemuria\Engine\Fantasya\Activity;
use Lemuria\Engine\Fantasya\ActivityProtocol;
use Lemuria\Engine\Fantasya\Command;
use Lemuria\Model\Fantasya\Party\Type;

class DefaultResolver
{
	/**
	 * @param array<Command> $defaults
	 */
	public function __construct(protected readonly ActivityProtocol $protocol, protected array $defaults) {
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
				if ($command instanceof Activity && !$command->IsAlternative()) {
					$other = $this->defaults[$second];
					if ($other instanceof Activity) {
						if ((string)$other === $phrase || !$command->allows($other)) {
							if (!$other->IsAlternative()) {
								unset($this->defaults[$second]);
								$n--;
								continue;
							}
						}
					}
				}
				$second++;
			}
			$first++;
		}
		return $this->promoteAlternative();
	}

	protected function promoteAlternative(): array {
		if ($this->protocol->Unit()->Party()->Type() === Type::Player && !$this->protocol->hasAlternativeActivity()) {
			$first = null;
			foreach ($this->defaults as $command) {
				if ($command instanceof Activity) {
					if (!$command->IsAlternative()) {
						return $this->defaults;
					}
					if (!$first) {
						$first = $command;
					}
				}
			}
			$first?->setAlternative(false);
		}
		return $this->defaults;
	}
}
