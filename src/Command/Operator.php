<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Command;

use Lemuria\Engine\Fantasya\Phrase;
use Lemuria\Model\Fantasya\Unicum;
use Lemuria\Model\Fantasya\Unit;

interface Operator
{
	public function ArgumentIndex(): int;

	public function Phrase(): Phrase;

	public function Unit(): Unit;

	public function Unicum(): Unicum;
}
