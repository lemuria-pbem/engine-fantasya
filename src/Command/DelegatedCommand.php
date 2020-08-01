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
	/**
	 * @var Command
	 */
	private Command $delegate;

	/**
	 * Create a new command for given Phrase.
	 *
	 * @param Phrase $phrase
	 * @param Context $context
	 */
	public function __construct(Phrase $phrase, Context $context) {
		parent::__construct($phrase, $context);
		$this->delegate = $this->createDelegate();
	}

	/**
	 * Get the delegate to execute.
	 *
	 * @return Command
	 */
	public function getDelegate(): Command {
		return $this->delegate->getDelegate();
	}

	/**
	 * Create the delegate.
	 *
	 * @return Command
	 */
	abstract protected function createDelegate(): Command;
}
