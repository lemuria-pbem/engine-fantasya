<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Message\Region\Event;

use function Lemuria\number;
use Lemuria\Engine\Fantasya\Message\Casus;
use Lemuria\Engine\Fantasya\Message\LemuriaMessage;
use Lemuria\Engine\Fantasya\Message\Region\AbstractRegionMessage;
use Lemuria\Engine\Message\Result;
use Lemuria\Item;

class DroughtRegionMessage extends AbstractRegionMessage
{
	protected Result $result = Result::Event;

	protected Item $trees;

	protected function create(): string {
		return 'A drought occurs, and in region ' . $this->id . ' ' . $this->trees . ' wither.';
	}

	protected function getData(LemuriaMessage $message): void {
		parent::getData($message);
		$this->trees = $message->getQuantity();
	}

	protected function getTranslation(string $name): string {
		if ($name === 'trees') {
			$trees = $this->trees->Count();
			return number($trees) . ' ' . $this->translateGrammar('Tree', Casus::Nominative, $trees === 1 ? 0 : 1);
		}
		return parent::getTranslation($name);
	}
}
