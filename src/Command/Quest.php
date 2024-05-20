<?php
declare (strict_types = 1);
namespace Lemuria\Engine\Fantasya\Command;

use Lemuria\Engine\Fantasya\Message\Unit\QuestAssignedMessage;
use Lemuria\Engine\Fantasya\Message\Unit\QuestNotAssignedMessage;
use Lemuria\Engine\Fantasya\Message\Unit\QuestNotFinishedMessage;
use Lemuria\Engine\Fantasya\Message\Unit\QuestNotHereMessage;
use Lemuria\Engine\Fantasya\Message\Unit\QuestUnknownMessage;
use Lemuria\Model\Exception\NotRegisteredException;
use Lemuria\Model\Fantasya\Scenario\Quest as QuestModel;

/**
 * Accept a quest or claim a reward.
 *
 * - AUFTRAG <quest>
 */
final class Quest extends UnitCommand
{
	protected function run(): void {
		$id = $this->parseId();
		try {
			$quest = QuestModel::get($id);
		} catch (NotRegisteredException) {
			$this->message(QuestUnknownMessage::class)->p((string)$id);
			return;
		}
		if ($quest->Owner()->Region() !== $this->unit->Region()) {
			$this->message(QuestNotHereMessage::class)->e($quest);
			return;
		}

		if ($this->context->getTurnOptions()->IsSimulation()) {
			return;
		}

		$controller = $quest->Controller()->setPayload($quest);
		$isAssigned = $controller->isAssignedTo($this->unit);
		if (!$isAssigned) {
			if ($controller->callFrom($this->unit)->isAssignedTo($this->unit)) {
				$this->message(QuestAssignedMessage::class)->e($quest);
			} else {
				$this->message(QuestNotAssignedMessage::class)->e($quest);
			}
		}
		if ($controller->isAssignedTo($this->unit)) {
			$canFinish = $controller->canBeFinishedBy($this->unit);
			if ($canFinish) {
				$controller->callFrom($this->unit)->isCompletedBy($this->unit);
			} elseif ($isAssigned) {
				$this->message(QuestNotFinishedMessage::class)->e($quest);
			}
		}
	}
}
