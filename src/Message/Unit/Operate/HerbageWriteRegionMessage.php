<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Message\Unit\Operate;

use Lemuria\Engine\Fantasya\Message\LemuriaMessage;
use Lemuria\Id;

class HerbageWriteRegionMessage extends HerbageWriteAllMessage
{
	public final const REGION = 'region';

	protected Id $region;

	protected function create(): string {
		return 'Unit ' . $this->id . ' writes the herb occurrence of region ' . $this->region . ' to the almanac ' . $this->almanac . '.';
	}

	protected function getData(LemuriaMessage $message): void {
		parent::getData($message);
		$this->region = $message->get(self::REGION);
	}
}
