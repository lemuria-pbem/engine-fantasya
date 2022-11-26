<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Combat;

/**
 * Binary flags for fighter features.
 */
enum Feature : int
{
	public final const SIZE = 256;

	case Shockwave = 1;

	case StoneSkin = 2;

	case GazeOfTheBasilisk = 4;

	case ZombieInfection = 8;
}
