<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Message\Vessel;

use Lemuria\Engine\Fantasya\Message\Reliability;

class EnterUnguardedPortMessage extends AbstractPortMessage
{
	protected Reliability $reliability = Reliability::Unreliable;

	protected string $type = 'unguarded';
}
