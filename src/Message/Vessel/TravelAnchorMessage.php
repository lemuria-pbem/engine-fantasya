<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Message\Vessel;

use Lemuria\Engine\Fantasya\Message\LemuriaMessage;

class TravelAnchorMessage extends TravelOverLandMessage
{
	public const ANCHOR = 'anchor';

	protected string $anchor;

	protected function create(): string {
		return 'Vessel ' . $this->id . ' is anchored in ' . $this->anchor . ' and cannot move to ' . $this->direction . '.';
	}

	protected function getData(LemuriaMessage $message): void {
		parent::getData($message);
		$this->anchor = $message->getParameter(self::ANCHOR);
	}
}
