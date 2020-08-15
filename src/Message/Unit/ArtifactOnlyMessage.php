<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Lemuria\Message\Unit;

class ArtifactOnlyMessage extends MaterialOnlyMessage
{
	/**
	 * @return string
	 */
	protected function create(): string {
		return 'Unit ' . $this->id . ' can only create ' . $this->output . ' with ' . $this->talent . '.';
	}
}