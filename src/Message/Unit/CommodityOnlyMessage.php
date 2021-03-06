<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Message\Unit;

class CommodityOnlyMessage extends RawMaterialOnlyMessage
{
	protected function create(): string {
		return 'Unit ' . $this->id . ' can only create ' . $this->output . ' with ' . $this->talent . '.';
	}
}
