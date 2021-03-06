<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Exception;

use JetBrains\PhpStorm\Pure;

use function Lemuria\getClass;
use Lemuria\Exception\LemuriaException;

class MessageEntityException extends LemuriaException
{
	#[Pure] public function __construct(string $name) {
		parent::__construct('Entity ' . getClass($name) . ' is not set.');
	}
}
