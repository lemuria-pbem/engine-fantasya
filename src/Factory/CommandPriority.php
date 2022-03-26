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
		'Presetting'       => 9,
		'Name'             => 11,
		'Describe'         => 12,
		'Disguise'         => 14,
		'Gather'           => 16,
		'Loot'             => 17,
		'Unguard'          => 18,
		'Fight'            => 19,
		'BattleSpell'      => 21,
		'Help'             => 23,
		'Contact'          => 24,
		'Announcement'     => 25,
		'Take'             => 26,
		'Read'             => 27,
		'Apply'            => 28,
		'Excert'           => 29,
		'Bestow'           => 30,
		'Give'             => 31,
		'Abandon'          => 33,
		'Enter'            => 34,
		'Board'            => 35,
		'Grant'            => 36,
		'Leave'            => 37,
		'Reserve'          => 39,
		'Dismiss'          => 41,
		'Lose'             => 42,
		'Cast'             => 44,
		'Griffinegg'       => 46,
		'Attack'           => 47,
		'Recruit'          => 49,
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
		"Road"             => 67,
		'Commodity'        => 69,
		'Herb'             => 70,
		'RawMaterial'      => 72,
		'Unknown'          => 74,
		'Tax'              => 76,
        'Entertain'        => 78,
		'Steal'            => 80,
		'Unicum'           => 82,
		'Write'            => 83,
		'Devastate'        => 84,
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
