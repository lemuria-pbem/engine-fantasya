<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Message\Unit;

class AllowKindMessage extends AllowCommodityMessage
{
	protected function create(): string {
		return 'Unit ' . $this->id . ' allows to trade ' . $this->commodity . '.';
	}

	protected function getTranslation(string $name): string {
		return $this->item($name, 'commodity') ?? parent::getTranslation($name);
	}
}
