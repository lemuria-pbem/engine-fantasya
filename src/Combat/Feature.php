<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Combat;

/**
 * Binary flags for fighter features.
 */
enum Feature : int
{
	public final const int SIZE = 256;

	case ShockWave = 1;

	case StoneSkin = 2;

	case GazeOfTheBasilisk = 4;

	case ZombieInfection = 8;
}
