<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Message\Party;

class PresettingExploreDepartMessage extends PresettingExploreMessage
{
	protected function create(): string {
		return 'Our units will land on foreign shores.';
	}
}
