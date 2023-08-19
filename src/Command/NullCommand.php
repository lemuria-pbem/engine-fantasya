<?php
declare (strict_types = 1);
namespace Lemuria\Engine\Fantasya\Command;

use Lemuria\Engine\Fantasya\Immediate;
use Lemuria\Lemuria;

/**
 * Dummy command for skipping ignored pseudo commands.
 *
 * - LOCALE
 * - REGION
 * - RUNDE
 */
final class NullCommand extends AbstractCommand implements Immediate
{
	public function skip(): static {
		return $this;
	}

	protected function run(): void {
		Lemuria::Log()->debug('Ignoring command ' . $this . '.');
	}
}
