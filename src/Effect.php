<?php
declare (strict_types = 1);
namespace Lemuria\Engine\Fantasya;

use Lemuria\Identifiable;
use Lemuria\Serializable;

/**
 * Effects are a result of events or player commands and can have a persisting influence.
 */
interface Effect extends Action, Identifiable, Serializable
{
	/**
	 * This flag can be set to execute newly created effects when the turn ends.
	 */
	public function needsAftercare(): bool;

	/**
	 * This flag can be set to execute the effect in a simulation run.
	 */
	public function supportsSimulation(): bool;
}
