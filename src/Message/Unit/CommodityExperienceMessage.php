<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Message\Unit;

use Lemuria\Engine\Fantasya\Message\LemuriaMessage;
use Lemuria\Engine\Message;
use Lemuria\Engine\Message\Section;
use Lemuria\Singleton;

class CommodityExperienceMessage extends AbstractUnitMessage
{
	public const TALENT = 't';

	public const ARTIFACT = 'a';

	protected string $level = Message::FAILURE;

	protected int $section = Section::PRODUCTION;

	protected Singleton $talent;

	protected Singleton $artifact;

	protected function create(): string {
		return 'Unit ' . $this->id . ' has not enough experience in ' . $this->talent . ' to create ' . $this->artifact . '.';
	}

	protected function getData(LemuriaMessage $message): void {
		parent::getData($message);
		$this->talent = $message->getSingleton(self::TALENT);
		$this->artifact = $message->getSingleton(self::ARTIFACT);
	}

	protected function getTranslation(string $name): string {
		$talent = $this->talent($name, 'talent');
		if ($talent) {
			return $talent;
		}
		$artifact = $this->commodity($name, 'artifact');
		if ($artifact) {
			return $artifact;
		}
		return parent::getTranslation($name);
	}
}
