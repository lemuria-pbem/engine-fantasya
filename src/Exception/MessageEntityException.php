<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Lemuria\Exception;

use function Lemuria\getClass;
use Lemuria\Exception\LemuriaException;

class MessageEntityException extends LemuriaException
{
	/**
	 * @param string $name
	 */
	public function __construct(string $name) {
		parent::__construct('Entity ' . getClass($name) . ' is not set.');
	}
}
