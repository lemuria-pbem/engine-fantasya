<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Message;

enum Reliability : int
{
	case Determined = 0;

	case Unreliable = 1;

	case Random = 2;
}
