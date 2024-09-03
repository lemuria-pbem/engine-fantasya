<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Message\Unit;

use Lemuria\Engine\Fantasya\Message\LemuriaMessage;

class QuotaSetHerbMessage extends QuotaRemoveHerbMessage
{
	protected float $quota;

	public function create(): string {
		return 'Unit ' . $this->id . ' sets a quota of ' . $this->quota . ' in region ' . $this->region . '.';
	}

	protected function getData(LemuriaMessage $message): void {
		parent::getData($message);
		$this->quota = $message->getParameter();
	}

	protected function getTranslation(string $name): string {
		return $this->percent($name, 'quota') ?? parent::getTranslation($name);
	}
}
