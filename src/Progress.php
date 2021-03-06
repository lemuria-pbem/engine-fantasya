<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya;

/**
 * The progress of the game world is defined by a number of events that are evaluated together with the commands of a
 * turn.
 */
interface Progress extends \Iterator
{
	/**
	 * Add an Event.
	 *
	 * @param Event $event
	 * @return Progress
	 */
	public function add(Event $event): Progress;
}
