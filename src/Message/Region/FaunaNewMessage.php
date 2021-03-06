<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Message\Region;

class FaunaNewMessage extends FaunaGrowthMessage
{
	protected function create(): string {
		return $this->animals . ' migrate to region ' . $this->id . '.';
	}
}
