<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Message\Unit\Operate;

use Lemuria\Engine\Fantasya\Message\LemuriaMessage;
use Lemuria\Id;

class HerbageApplyMessage extends HerbageApplyAllMessage
{
	public final const REGION = 'region';

	protected Id $region;

	protected function create(): string {
		return 'Unit ' . $this->id . ' adds the herb occurrence of region ' . $this->region . ' from almanac ' . $this->almanac . '.';
	}

	protected function getData(LemuriaMessage $message): void {
		parent::getData($message);
		$this->region = $message->get(self::REGION);
	}
}
