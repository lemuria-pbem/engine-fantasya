<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Factory\Model;

enum Visibility : int
{
	case UNKNOWN = 0;

	case HISTORIC = 1;

	case NEIGHBOUR = 2;

	case LIGHTHOUSE = 3;

	case TRAVELLED = 4;

	case FARSIGHT = 5;

	case WITH_UNIT = 6;
}
