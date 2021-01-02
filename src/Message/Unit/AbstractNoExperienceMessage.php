<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Lemuria\Message\Unit;

use Lemuria\Engine\Message;
use Lemuria\Model\Lemuria\Factory\BuilderTrait;
use Lemuria\Model\Lemuria\Talent;
use Lemuria\Singleton;

abstract class AbstractNoExperienceMessage extends AbstractUnitMessage
{
	use BuilderTrait;

	protected string $level = Message::FAILURE;

	protected Singleton $talent;

	public function __construct() {
		$this->talent = $this->getTalent();
	}

	protected function create(): string {
		return 'Unit ' . $this->id . ' has no experience in ' . $this->talent . '.';
	}

	abstract protected function getTalent(): Talent;

	protected function getTranslation(string $name): string {
		return $this->talent($name, 'talent') ?? parent::getTranslation($name);
	}
}
