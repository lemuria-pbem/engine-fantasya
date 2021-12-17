<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Message\Vessel;

class EnterUnguardedPortMessage extends AbstractPortMessage
{
	protected string $type = 'unguarded';
}
