<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Message\Vessel;

use Lemuria\Engine\Fantasya\Message\LemuriaMessage;

class TravelAnchorMessage extends TravelOverLandMessage
{
	public final const string ANCHOR = 'anchor';

	protected string $anchor;

	protected function create(): string {
		return 'Vessel ' . $this->id . ' is anchored in ' . $this->anchor . ' and cannot move to ' . $this->direction . '.';
	}

	protected function getData(LemuriaMessage $message): void {
		parent::getData($message);
		$this->anchor = $message->getParameter(self::ANCHOR);
	}

	protected function getTranslation(string $name): string {
		return $this->direction($name, self::ANCHOR) ?? parent::getTranslation($name);
	}
}
