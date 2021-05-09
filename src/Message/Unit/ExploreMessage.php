<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Message\Unit;

use Lemuria\Engine\Fantasya\Message\LemuriaMessage;
use Lemuria\Singleton;

class ExploreMessage extends ExploreNothingMessage
{
	protected Singleton $herb;

	protected string $occurrence;

	protected function create(): string {
		return 'Unit ' . $this->id . ' has found ' . $this->occurrence . ' ' . $this->herb . ' in region ' . $this->region . '.';
	}

	protected function getData(LemuriaMessage $message): void {
		parent::getData($message);
		$this->herb       = $message->getSingleton();
		$this->occurrence = $message->getParameter();
	}

	protected function getTranslation(string $name): string {
		if ($name === 'herb') {
			return $this->commodity($name, 'herb', 1);
		}
		if ($name === 'occurrence') {
			return $this->translateKey('amount.' . $this->occurrence);
		}
		return parent::getTranslation($name);
	}
}
