<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Combat;

enum Side
{
	/**
	 * Attacker is attacking regularly.
	 */
	case Attacker;

	/**
	 * Attacker involves defender into previous attack.
	 */
	case Involve;

	/**
	 * Attacker must defend against previous attack.
	 */
	case Defender;
}
