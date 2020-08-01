<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Lemuria\Message\Unit;

use Lemuria\Engine\Message;

class RawMaterialNoDemandMessage extends RawMaterialResourcesMessage
{
	protected string $level = Message::DEBUG;

	/**
	 * @return string
	 */
	protected function create(): string {
		return 'Unit ' . $this->id . ' cannot produce ' . $this->material . ', no demand.';
	}
}
