<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Message\Vessel;

class EnterUnmaintainedPortMessage extends AbstractPortMessage
{
	protected string $type = 'unmaintained';
}
