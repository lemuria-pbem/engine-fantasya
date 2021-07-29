<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Message\Unit;

use Lemuria\Engine\Fantasya\Message\LemuriaMessage;

class CastOnlyMessage extends CastExperienceMessage
{
	protected int $maximum;

	protected function create(): string {
		return 'Unit ' . $this->id . ' can only cast ' . $this->spell . ' on level ' . $this->maximum . '.';
	}

	protected function getData(LemuriaMessage $message): void {
		parent::getData($message);
		$this->maximum = $message->getParameter();
	}

	protected function getTranslation(string $name): string {
		return $this->number($name, 'maximum') ?? parent::getTranslation($name);
	}
}
