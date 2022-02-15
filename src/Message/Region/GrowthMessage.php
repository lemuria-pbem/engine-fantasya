<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Message\Region;

use Lemuria\Engine\Fantasya\Message\LemuriaMessage;
use Lemuria\Engine\Message\Section;
use Lemuria\Singleton;

class GrowthMessage extends AbstractRegionMessage
{
	protected Section $section = Section::ECONOMY;

	protected int $trees;

	protected Singleton $tree;

	protected function create(): string {
		return 'In region ' . $this->id . ' ' . $this->trees . ' trees are growing.';
	}

	protected function getData(LemuriaMessage $message): void {
		parent::getData($message);
		$this->trees = $message->getParameter();
		$this->tree  = $message->getSingleton();
	}

	protected function getTranslation(string $name): string {
		if ($name === 'tree') {
			return parent::translateKey('replace.tree', $this->trees > 1 ? 1 : 0);
		}
		return $this->number($name, 'trees') ?? parent::getTranslation($name);
	}
}
