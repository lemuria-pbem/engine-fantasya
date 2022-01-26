<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Message\Unit;

use Lemuria\Engine\Fantasya\Message\LemuriaMessage;
use Lemuria\Engine\Message;
use Lemuria\Engine\Message\Section;
use Lemuria\Singleton;

class RawMaterialExperienceMessage extends AbstractUnitMessage
{
	public const TALENT = 'talent';

	public const MATERIAL = 'material';

	protected string $level = Message::FAILURE;

	protected Section $section = Section::PRODUCTION;

	protected Singleton $talent;

	protected Singleton $material;

	protected function create(): string {
		return 'Unit ' . $this->id . ' has not enough experience in ' . $this->talent . ' to produce ' . $this->material . '.';
	}

	protected function getData(LemuriaMessage $message): void {
		parent::getData($message);
		$this->talent = $message->getSingleton(self::TALENT);
		$this->material = $message->getSingleton(self::MATERIAL);
	}

	protected function getTranslation(string $name): string {
		$material = $this->commodity($name, 'material');
		if ($material) {
			return $material;
		}
		$talent = $this->talent($name, 'talent');
		if ($talent) {
			return $talent;
		}
		return parent::getTranslation($name);
	}
}
