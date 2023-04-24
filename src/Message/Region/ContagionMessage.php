<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Message\Region;

use Lemuria\Engine\Fantasya\Message\LemuriaMessage;
use Lemuria\Engine\Message\Result;

class ContagionMessage extends AbstractRegionMessage
{
	protected Result $result = Result::Event;

	protected string $disease;

	protected function create(): string {
		return 'The ' . $this->disease . ' disease strikes the region.';
	}

	protected function getData(LemuriaMessage $message): void {
		parent::getData($message);
		$this->disease = $message->getParameter();
	}

	protected function getTranslation(string $name): string {
		if ($name === 'disease') {
			return $this->translateKey('disease.' . $this->disease);
		}
		return parent::getTranslation($name);
	}
}
