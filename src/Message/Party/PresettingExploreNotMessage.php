<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Message\Party;

class PresettingExploreNotMessage extends PresettingExploreMessage
{
	protected function create(): string {
		return 'Our units will strictly follow their routes.';
	}
}
