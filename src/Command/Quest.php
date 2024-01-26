<?php
declare (strict_types = 1);
namespace Lemuria\Engine\Fantasya\Command;

use Lemuria\Engine\Fantasya\Message\Unit\QuestAssignedMessage;
use Lemuria\Engine\Fantasya\Message\Unit\QuestChoiceMessage;
use Lemuria\Engine\Fantasya\Message\Unit\QuestFinishedMessage;
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
		if ($quest->Unit()->Region() !== $this->unit->Region()) {
			$this->message(QuestNotHereMessage::class)->e($quest);
			return;
		}

		$controller = $quest->Controller();
		if ($controller->isAssignedTo($this->unit)) {
			$canFinish = $controller->canBeFinishedBy($this->unit);
			if ($canFinish) {
				if ($controller->callFrom($this->unit)->isCompletedBy($this->unit)) {
					$this->message(QuestFinishedMessage::class)->e($quest);
				} else {
					$this->message(QuestChoiceMessage::class)->e($quest);
				}
			} else {
				$this->message(QuestNotFinishedMessage::class)->e($quest);
			}
		} else {
			if ($controller->callFrom($this->unit)->isAssignedTo($this->unit)) {
				$this->message(QuestAssignedMessage::class)->e($quest);
			} else {
				$this->message(QuestNotAssignedMessage::class)->e($quest);
			}
		}
	}
}
