<?php
declare (strict_types = 1);
namespace Lemuria\Engine\Fantasya\Factory;

use function Lemuria\getClass;
use Lemuria\Engine\Fantasya\Action;
use Lemuria\Engine\Fantasya\Command;
use Lemuria\Engine\Fantasya\Effect;
use Lemuria\Engine\Fantasya\Event;
use Lemuria\Engine\Fantasya\Priority;
use Lemuria\Exception\LemuriaException;

/**
 * Here the execution priority of all commands is determined.
 */
final class CommandPriority
{
	/**
	 * Execution order of all command classes.
	 */
	public const ORDER = [
		// 0 forbidden
		'EFFECT_BEFORE'    => 1,
		'EVENT_BEFORE'     => 2,
		'DefaultCommand'   => 3,
		'Copy'             => 4,
		'Origin'           => 6,
		'Banner'           => 7,
		'Name'             => 8,
		'Describe'         => 9,
		'Disguise'         => 11,
		'Gather'           => 12,
		'Loot'             => 13,
		'Unguard'          => 15,
		'Fight'            => 17,
		'BattleSpell'      => 18,
		'Help'             => 20,
		'Contact'          => 21,
		'Announcement'     => 23,
		'Read'             => 25,
		'Apply'            => 26,
		'Excert'           => 27,
		'Bestow'           => 29,
		'Give'             => 30,
		'Abandon'          => 32,
		'Enter'            => 33,
		'Board'            => 34,
		'Grant'            => 35,
		'Leave'            => 36,
		'Reserve'          => 38,
		'Dismiss'          => 40,
		'Lose'             => 41,
		'Griffinegg'       => 44,
		'Attack'           => 45,
		'Recruit'          => 47,
		'Cast'             => 49,
		'EFFECT_MIDDLE'    => 50,
		'EVENT_MIDDLE'     => 51,
		'Siege'            => 52,
		'Smash'            => 54,
		'Teach'            => 56,
		'Learn'            => 57,
		'Operate'          => 58,
		'Spy'              => 60,
		'Sell'             => 62,
		'Buy'              => 63,
		'Construction'     => 65,
		'Vessel'           => 66,
		"Road"             => 68,
		'Commodity'        => 70,
		'Herb'             => 72,
		'RawMaterial'      => 73,
		'Unknown'          => 75,
		'Tax'              => 77,
        'Entertain'        => 79,
		'Steal'            => 81,
		'Unicum'           => 83,
		'Write'            => 84,
		'Travel'           => 86,
		'Route'            => 87,
		'Follow'           => 88,
		'Explore'          => 90,
		'Guard'            => 92,
		'Sort'             => 94,
		'Number'           => 95,
		'Comment'          => 96,
		'Migrate'          => 97,
		'EFFECT_AFTER'     => 98,
		'EVENT_AFTER'      => 99,
		'Initiate'         => 100
	];

	/**
	 * Priority of B-Events.
	 */
	private const B_ACTION = 2;

	/**
	 * Priority of M-Events.
	 */
	private const M_ACTION = 51;

	/**
	 * Priority of A-Events.
	 */
	private const A_ACTION = 99;

	private static ?CommandPriority $instance = null;

	public static function getInstance(): CommandPriority {
		if (!self::$instance) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	/**
	 * Get the priority of an Action.
	 *
	 * @throws LemuriaException
	 */
	public function getPriority(Action $action): int {
		if ($action instanceof Command) {
			$class = getClass($action);
			if (isset(self::ORDER[$class])) {
				return self::ORDER[$class];
			}
		}

		$priority = match ($action->Priority()) {
			Priority::BEFORE => self::B_ACTION,
			Priority::MIDDLE => self::M_ACTION,
			Priority::AFTER  => self::A_ACTION
		};

		return match (true) {
			$action instanceof Effect => --$priority,
			$action instanceof Event  => $priority,
			default                   => throw new LemuriaException('Unsupported action: ' . getClass($action))
		};
	}

	/**
	 * Determine execution order.
	 */
	public function compare(Command $command1, Command $command2): int {
		$priority1 = $this->getPriority($command1);
		$priority2 = $this->getPriority($command2);
		if ($priority1 < $priority2) {
			return -1;
		}
		if ($priority1 > $priority2) {
			return 1;
		}
		return 0;
	}

	/**
	 * Constructor is private in this singleton class.
	 */
	private function __construct() {
	}
}
