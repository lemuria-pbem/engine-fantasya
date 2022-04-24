<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Command\Exception;

use Lemuria\Engine\Fantasya\Exception\CommandException;
use Lemuria\Model\Fantasya\Practice;
use Lemuria\Model\Fantasya\Unicum;

class UnsupportedOperateException extends CommandException
{
	public function __construct(Unicum $unicum, Practice $practice) {
		parent::__construct($unicum->Composition() . ' ' . $unicum->Id() . ' does not support ' . $practice->name . '.');
	}
}
