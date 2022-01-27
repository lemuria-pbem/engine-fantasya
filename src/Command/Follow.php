<?php
declare (strict_types = 1);
namespace Lemuria\Engine\Fantasya\Command;

use Lemuria\Engine\Fantasya\Exception\UnknownCommandException;
use Lemuria\Engine\Fantasya\Factory\DefaultActivityTrait;
use Lemuria\Engine\Fantasya\Message\Unit\FollowMessage;
use Lemuria\Engine\Fantasya\Message\Unit\FollowNoMoveMessage;
use Lemuria\Model\Fantasya\Unit;

/**
 * Implementation of command FOLGEN (follow other unit).
 *
 * - FOLGEN <unit>
 */
final class Follow extends Travel
{
	use DefaultActivityTrait;

	private ?Unit $leader;

	protected function run(): void {
		if ($this->directions->count()) {
			$this->message(FollowMessage::class)->e($this->leader);
			parent::run();
			return;
		}
		$this->message(FollowNoMoveMessage::class)->e($this->leader);
	}

	protected function initDirections(): void {
		if ($this->phrase->count() !== 1) {
			throw new UnknownCommandException($this);
		}
		$i            = 1;
		$this->leader = $this->nextId($i);
		if ($this->calculus()->canDiscover($this->leader)) {
			$route = $this->context->getTravelRoute($this->leader);
			while ($route->hasMore()) {
				$this->directions->add($route->next()->value);
			}
		}
	}

	protected function addToTravelRoute(string $direction): void {
	}
}
