<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Lemuria\Message\Unit;

use Lemuria\Engine\Message;

class ProductMaterialOnlyMessage extends ProductOutputMessage
{
	protected string $level = Message::FAILURE;

	/**
	 * @return string
	 */
	protected function create(): string {
		return 'Unit ' . $this->id . ' can only produce ' . $this->output . ' with ' . $this->talent . '.';
	}
}
