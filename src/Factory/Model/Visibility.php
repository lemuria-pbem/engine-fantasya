<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Factory\Model;

enum Visibility
{
	case UNKNOWN;

	case HISTORIC;

	case NEIGHBOUR;

	case LIGHTHOUSE;

	case TRAVELLED;

	case WITH_UNIT;
}
