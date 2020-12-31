<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Lemuria\Message\Exception;

use JetBrains\PhpStorm\Pure;

use Lemuria\Engine\Lemuria\Message\LemuriaMessage;

class EntityNotSetException extends \UnexpectedValueException
{
	#[Pure] public function __construct(LemuriaMessage $message, string $name) {
		parent::__construct('Message ' . $message->Id() . ' has no entity ' . $name . '.');
	}
}
