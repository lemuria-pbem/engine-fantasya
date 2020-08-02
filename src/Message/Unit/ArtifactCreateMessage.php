<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Lemuria\Message\Unit;

class ArtifactCreateMessage extends MaterialOutputMessage
{
	/**
	 * @return string
	 */
	protected function create(): string {
		return 'Unit ' . $this->id . ' creates ' . $this->output . ' with ' . $this->talent . '.';
	}
}
