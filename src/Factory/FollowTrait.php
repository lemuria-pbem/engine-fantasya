<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Factory;

use Lemuria\Engine\Fantasya\Effect\FollowEffect;
use Lemuria\Engine\Fantasya\Message\Unit\FollowerMessage;
use Lemuria\Engine\Fantasya\Message\Unit\FollowerNotMessage;
use Lemuria\Engine\Fantasya\Message\Unit\FollowingMessage;
use Lemuria\Engine\Fantasya\Message\Unit\FollowingNotMessage;
use Lemuria\Engine\Fantasya\State;
use Lemuria\Lemuria;
use Lemuria\Model\Fantasya\Extension\Followers;
use Lemuria\Model\Fantasya\Unit;

trait FollowTrait
{
	private function getExistingFollower(Unit $follower): ?FollowEffect {
		$follow = new FollowEffect(State::getInstance());
		$follow = Lemuria::Score()->find($follow->setUnit($follower));
		return $follow instanceof FollowEffect ? $follow : null;
	}

	private function startFollowing(Unit $leader, Unit $follower): void {
		$follow = new FollowEffect(State::getInstance());
		Lemuria::Score()->add($follow->setUnit($follower)->setLeader($leader));
		Lemuria::Log()->debug($follower . ' will follow ' . $leader . ' from now on.');
		/** @var Followers $followers */
		$followers = $leader->Extensions()->init(Followers::class);
		$followers->Followers()->add($follower);
		$this->message(FollowerMessage::class, $follower)->e($leader);
		$this->message(FollowingMessage::class, $leader)->e($follower);
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
}
