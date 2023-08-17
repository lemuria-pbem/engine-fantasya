<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Event\Act;

use Lemuria\Engine\Fantasya\Effect\HibernateEffect;
use Lemuria\Engine\Fantasya\Event\Act;
use Lemuria\Engine\Fantasya\Event\ActTrait;
use Lemuria\Engine\Fantasya\Factory\MessageTrait;
use Lemuria\Engine\Fantasya\Message\Unit\Act\HibernateMessage;
use Lemuria\Engine\Fantasya\State;
use Lemuria\Lemuria;
use Lemuria\Model\Fantasya\Factory\BuilderTrait;

/**
 * Hibernating animals disappear and sleep.
 */
class Hibernate implements Act
{
	use ActTrait;
	use BuilderTrait;
	use MessageTrait;

	public function act(): static {
		$effect = new HibernateEffect(State::getInstance());
		if (!Lemuria::Score()->find($effect->setUnit($this->unit))) {
			Lemuria::Score()->add($effect);
		}
		$this->message(HibernateMessage::class, $this->unit);
		return $this;
	}
}
