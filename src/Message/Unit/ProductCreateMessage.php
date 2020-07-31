<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Lemuria\Message\Unit;

class ProductCreateMessage extends ProductOutputMessage
{
	/**
	 * @return string
	 */
	protected function create(): string {
		return 'Unit ' . $this->Id() . ' creates ' . $this->output . ' with ' . $this->talent . '.';
	}
}
