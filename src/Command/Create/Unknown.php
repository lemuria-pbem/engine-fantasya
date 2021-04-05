<?php
declare (strict_types = 1);
namespace Lemuria\Engine\Fantasya\Command\Create;

use Lemuria\Engine\Fantasya\Command\UnitCommand;
use Lemuria\Engine\Fantasya\Context;
use Lemuria\Engine\Fantasya\Exception\UnknownItemException;
use Lemuria\Engine\Fantasya\Phrase;

/**
 * This is a dummy command that delays the unknown item exception until the evaluation phase.
 */
final class Unknown extends UnitCommand
{
	public function __construct(Phrase $phrase, Context $context, private UnknownItemException $exception) {
		parent::__construct($phrase, $context);
	}

	protected function run(): void {
		throw $this->exception;
	}
}
