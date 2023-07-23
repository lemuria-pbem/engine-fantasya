<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Message\Unit;

use Lemuria\Engine\Fantasya\Message\LemuriaMessage;
use Lemuria\Singleton;

class QuotaRemoveMessage extends QuotaRemoveHerbMessage
{
	protected Singleton $commodity;

	public function create(): string {
		return 'Unit ' . $this->id . ' removes the quota for ' . $this->commodity . ' in region ' . $this->region . '.';
	}

	protected function getData(LemuriaMessage $message): void {
		parent::getData($message);
		$this->commodity = $message->getSingleton();
	}

	protected function getTranslation(string $name): string {
		return $this->singleton($name, 'commodity') ?? parent::getTranslation($name);
	}
}
