<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Lemuria\Message\Unit;

use Lemuria\Engine\Lemuria\Message\LemuriaMessage;
use Lemuria\Id;

class HelpRegionMessage extends HelpMessage
{
	public const REGION = 'region';

	protected Id $region;

	protected function create(): string {
		return 'Unit ' . $this->id . ' has set relation ' . $this->agreement . ' to all parties in region ' . $this->region . '.';
	}

	protected function getData(LemuriaMessage $message): void {
		parent::getData($message);
		$this->region = $message->get(self::REGION);
	}
}
