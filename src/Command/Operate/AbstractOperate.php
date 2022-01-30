<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Command\Operate;

use Lemuria\Engine\Fantasya\Command\Unicum;
use Lemuria\Engine\Fantasya\Factory\MessageTrait;

abstract class AbstractOperate
{
	use MessageTrait;

	public function __construct(protected Unicum $unicum) {
		//TODO Unicum
	}

	protected function apply(): void {
	}

	protected function read(): void {
	}

	protected function write(): void {
	}
}
