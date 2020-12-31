<?php
declare (strict_types = 1);
namespace Lemuria\Engine\Lemuria\Command;

use Lemuria\Engine\Lemuria\Command;
use Lemuria\Engine\Lemuria\Context;
use Lemuria\Engine\Lemuria\Phrase;

/**
 * Base class for all complex commands that delegate to simpler commands.
 */
abstract class DelegatedCommand extends UnitCommand
{
	private Command $delegate;

	/**
	 * Create a new command for given Phrase.
	 */
	public function __construct(Phrase $phrase, Context $context) {
		parent::__construct($phrase, $context);
		$this->delegate = $this->createDelegate();
	}

	/**
	 * Get the delegate to execute.
	 */
	public function getDelegate(): Command {
		return $this->delegate->getDelegate();
	}

	/**
	 * Create the delegate.
	 */
	abstract protected function createDelegate(): Command;
}
