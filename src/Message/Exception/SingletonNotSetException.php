<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Message\Exception;

use JetBrains\PhpStorm\Pure;

use Lemuria\Engine\Fantasya\Message\LemuriaMessage;

class SingletonNotSetException extends \UnexpectedValueException
{
	#[Pure] public function __construct(LemuriaMessage $message, string $name) {
		parent::__construct('Message ' . $message->Id() . ' has no singleton ' . $name . '.');
	}
}
