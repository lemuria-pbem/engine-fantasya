<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Lemuria\Exception;

use Lemuria\Engine\Message;
use Lemuria\Model\Exception\ModelException;

class DuplicateMessageException extends ModelException
{
	/**
	 * @param Message $message
	 */
	public function __construct(Message $message) {
		parent::__construct('Report message ' . $message->Id() . ' is already registered.');
	}
}
