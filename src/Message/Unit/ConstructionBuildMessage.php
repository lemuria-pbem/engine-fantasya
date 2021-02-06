<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Lemuria\Message\Unit;

use Lemuria\Engine\Message;

class ConstructionBuildMessage extends ConstructionOnlyMessage
{
	protected string $level = Message::SUCCESS;

	protected function create(): string {
		return 'Unit ' . $this->id . ' builds ' . $this->size . ' points in size on construction ' . $this->construction . '.';
	}
}
