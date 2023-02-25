<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Exception\Command;

use Lemuria\Engine\Fantasya\Exception\CommandException;
use Lemuria\Engine\Fantasya\Message\Exception;

class PartyAlreadySetException extends CommandException
{
	public function __construct() {
		parent::__construct('Party has been set already.');
		$this->translationKey = Exception::PartyAlreadySet;
	}
}
