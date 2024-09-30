<?php
/** @noinspection PhpUnusedPrivateMethodInspection */
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Factory;

use Lemuria\Engine\Fantasya\Command\Follow;
use Lemuria\Engine\Fantasya\Command\Trespass\Board;
use Lemuria\Engine\Fantasya\Command\Trespass\Enter;
use Lemuria\Engine\Fantasya\Context;
use Lemuria\Engine\Fantasya\Effect\FollowEffect;
use Lemuria\Engine\Fantasya\Message\Unit\FollowerMessage;
use Lemuria\Engine\Fantasya\Message\Unit\FollowerNotMessage;
use Lemuria\Engine\Fantasya\Message\Unit\FollowingMessage;
use Lemuria\Engine\Fantasya\Message\Unit\FollowingNotMessage;
use Lemuria\Engine\Fantasya\Phrase;
use Lemuria\Engine\Fantasya\State;
use Lemuria\Lemuria;
use Lemuria\Model\Fantasya\Extension\Followers;
use Lemuria\Model\Fantasya\Unit;

trait FollowTrait
{
	use MessageTrait;

	private function getExistingFollower(Unit $follower): ?FollowEffect {
		$follow = new FollowEffect(State::getInstance());
		$follow = Lemuria::Score()->find($follow->setUnit($follower));
		return $follow instanceof FollowEffect ? $follow : null;
	}

	private function startFollowing(Unit $leader, Unit $follower): void {
		$follow = new FollowEffect(State::getInstance());
		Lemuria::Score()->add($follow->setUnit($follower)->setLeader($leader)->addReassignment());
		Lemuria::Log()->debug($follower . ' will follow ' . $leader . ' from now on.');

		/** @var Followers $followers */
		$followers = $leader->Extensions()->init(Followers::class);
		$followers->Followers()->add($follower);

		$state   = State::getInstance();
		$context = new Context($state);
		$command = new Follow(new Phrase('FOLGEN ' . $leader->Id()), $context->setUnit($follower));
		$state->injectIntoTurn($command);

		$this->message(FollowerMessage::class, $leader)->e($follower);
		$this->message(FollowingMessage::class, $follower)->e($leader);
	}

	private function ceaseFollowing(FollowEffect $follow, Unit $follower): void {
		$leader     = $follow->Leader();
		$extensions = $leader->Extensions();
		if (isset($extensions[Followers::class])) {
			/** @var Followers $followers */
			$followers = $extensions[Followers::class];
			if ($followers->Followers()->has($follower->Id())) {
				$followers->Followers()->remove($follower);
				$this->message(FollowingNotMessage::class, $follower)->e($leader);
				$this->message(FollowerNotMessage::class, $leader)->e($follower);
			}
		}
		Lemuria::Score()->remove($follow);
		Lemuria::Log()->debug($follower . ' will not follow ' . $leader . ' any longer.');
	}

	private function enterForLeader(Unit $leader, Unit $follower): void {
		$construction = $leader->Construction();
		if ($construction && $construction !== $follower->Construction()) {
			$state   = State::getInstance();
			$context = new Context($state);
			$command = new Enter(new Phrase('BETRETEN ' . $construction->Id()), $context->setUnit($follower));
			$state->injectIntoTurn($command);
		} else {
			$vessel = $leader->Vessel();
			if ($vessel && $vessel !== $follower->Vessel()) {
				$state   = State::getInstance();
				$context = new Context($state);
				$command = new Board(new Phrase('BESTEIGEN ' . $vessel->Id()), $context->setUnit($follower));
				$state->injectIntoTurn($command);
			}
		}
	}
}
