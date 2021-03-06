<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Lemuria\Message\Region;

class FaunaMigrantsMessage extends FaunaGrowthMessage
{
	protected function create(): string {
		return $this->animals . ' leave region ' . $this->id . '.';
	}
}
