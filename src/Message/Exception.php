<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Message;

enum Exception
{
	case None;

	case InvalidId;

	case TempIdExists;

	case TempUnitNotMapped;

	case UnitNotFound;
}
