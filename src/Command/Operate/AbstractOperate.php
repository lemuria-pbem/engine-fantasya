<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Command\Operate;

use Lemuria\Engine\Fantasya\Command\Exception\UnsupportedOperateException;
use Lemuria\Engine\Fantasya\Command\Use\Operate;
use Lemuria\Engine\Fantasya\Factory\MessageTrait;
use Lemuria\Model\Fantasya\Practice;

abstract class AbstractOperate
{
	use MessageTrait;

	public function __construct(protected Operate $operate) {
		//TODO Unicum
	}

	public function apply(): void {
		throw new UnsupportedOperateException($this->operate->Unicum(), Practice::APPLY);
	}

	public function give(): void {
		throw new UnsupportedOperateException($this->operate->Unicum(), Practice::GIVE);
	}

	public function read(): void {
		throw new UnsupportedOperateException($this->operate->Unicum(), Practice::READ);
	}

	public function write(): void {
		throw new UnsupportedOperateException($this->operate->Unicum(), Practice::WRITE);
	}
}
