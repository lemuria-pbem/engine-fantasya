<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Message\Unit;

use Lemuria\Engine\Fantasya\Message\LemuriaMessage;
use Lemuria\Engine\Message;
use Lemuria\Engine\Message\Section;

class TaxDemandMessage extends AbstractUnitMessage
{
	public const COLLECTORS = 'collectors';

	public const RATE = 'rate';

	protected string $level = Message::DEBUG;

	protected Section $section = Section::PRODUCTION;

	protected int $collectors;

	protected int $rate;

	protected function create(): string {
		return 'Unit ' . $this->id . ' has ' . $this->collectors . ' tax collectors with demand of ' . $this->rate . ' silver.';
	}

	protected function getData(LemuriaMessage $message): void {
		parent::getData($message);
		$this->collectors = $message->getParameter(self::COLLECTORS);
		$this->rate = $message->getParameter(self::RATE);
	}

	protected function getTranslation(string $name): string {
		$collectors = $this->number($name, self::COLLECTORS);
		if ($collectors) {
			return $collectors;
		}
		$rate = $this->number($name, self::RATE);
		if ($rate) {
			return $rate;
		}
		return parent::getTranslation($name);
	}
}
