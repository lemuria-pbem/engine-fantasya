<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Message;

enum Exception
{
	case None;

	case InvalidAlternative;

	case InvalidCommand;

	case InvalidDefault;

	case InvalidId;

	case PartyAlreadySet;

	case TempIdExists;

	case TempUnitNotMapped;

	case UnitNotFound;

	case UnknownItem;
}
