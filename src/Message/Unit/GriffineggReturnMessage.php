<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Message\Unit;

use Lemuria\Engine\Fantasya\Message\Casus;
use Lemuria\Engine\Fantasya\Message\LemuriaMessage;
use Lemuria\Item;

class GriffineggReturnMessage extends GriffineggReturnsMessage
{
	protected Item $griffins;

	protected function create(): string {
		return $this->griffins . ' of unit ' . $this->id . ' return to their aeries in region ' . $this->region . '.';
	}

	protected function getData(LemuriaMessage $message): void {
		parent::getData($message);
		$this->griffins = $message->getQuantity();
	}

	protected function getTranslation(string $name): string {
		return $this->item($name, 'griffins', $this->index(), Casus::Dative) ?? parent::getTranslation($name);
	}

	protected function index(): int {
		return $this->griffins->Count() === 1 ? 0 : 1;
	}
}
