<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Factory\Model;

enum Visibility : int
{
	case Unknown = 0;

	case Historic = 1;

	case Neighbour = 2;

	case Lighthouse = 3;

	case Travelled = 4;

	case Farsight = 5;

	case WithUnit = 6;
}
