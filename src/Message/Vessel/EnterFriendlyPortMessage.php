<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Message\Vessel;

class EnterFriendlyPortMessage extends AbstractPortMessage
{
	protected string $type = 'friendly';
}
