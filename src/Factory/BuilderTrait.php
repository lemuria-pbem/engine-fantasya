<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Factory;

use Lemuria\Exception\SingletonException;
use Lemuria\Lemuria;
use Lemuria\Engine\Fantasya\Message\MessageType;

trait BuilderTrait
{
	/**
	 * Create a message type singleton.
	 *
	 * @throws SingletonException
	 */
	protected static function createMessageType(string $class): MessageType {
		$messageType = Lemuria::Builder()->create($class);
		if ($messageType instanceof MessageType) {
			return $messageType;
		}
		throw new SingletonException($class, 'message type');
	}
}
