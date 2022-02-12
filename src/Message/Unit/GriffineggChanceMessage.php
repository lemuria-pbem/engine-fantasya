<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Message\Unit;

use Lemuria\Engine\Fantasya\Message\LemuriaMessage;
use Lemuria\Engine\Message\Section;

class GriffineggChanceMessage extends GriffineggOnlyMessage
{
	public const CHANCE = 'chance';

	protected Section $section = Section::PRODUCTION;

	protected float $chance;

	protected function create(): string {
		return 'Unit ' . $this->id . ' has a chance of ' . $this->chance . ' to steal ' . $this->eggs . '.';
	}

	protected function getData(LemuriaMessage $message): void {
		parent::getData($message);
		$this->chance = $message->getParameter(self::CHANCE);
	}
}
