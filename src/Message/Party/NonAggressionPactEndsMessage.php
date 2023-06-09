<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Message\Party;

class NonAggressionPactEndsMessage extends NonAggressionPactLastMessage
{
	protected function create(): string {
		return 'Your units are not protected from attacks anymore.';
	}
}
