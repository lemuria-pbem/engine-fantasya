<?php
declare (strict_types = 1);
namespace Lemuria\Engine\Fantasya\Combat;

enum Stake
{
	case Ally;

	case Attacker;

	case Defender;

	case Neutral;
}
