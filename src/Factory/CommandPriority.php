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
	public final const ORDER = [
		// 0 forbidden
		'EFFECT_BEFORE'  => 1,
		'EVENT_BEFORE'   => 2,
		'DefaultCommand' => 3,
		'Copy'           => 4,
		'Alternative'    => 5,
		'Origin'         => 6,
		'Banner'         => 7,
		'Presetting'     => 8,
		'Realm'          => 9,
		'Name'           => 10,
		'Describe'       => 11,
		'Rumor'          => 12,
		'Disguise'       => 13,
		'Gather'         => 14,
		'Loot'           => 15,
		'Block'          => 16,
		'Unguard'        => 17,
		'Fight'          => 18,
		'BattleSpell'    => 19,
		'Help'           => 20,
		'Contact'        => 21,
		'Visit'          => 22,
		'Announcement'   => 23,
		'Take'           => 24,
		'Read'           => 25,
		'Apply'          => 26,
		'Excert'         => 27,
		'Bestow'         => 28,
		'Give'           => 29,
		'Repeat'         => 30,
		'Accept'         => 31,
		'Cancel'         => 32,
		'Fee'            => 33,
		'Forbid'         => 34,
		'Allow'          => 35,
		'Quota'          => 36,
		'Abandon'        => 37,
		'Enter'          => 38,
		'Board'          => 39,
		'Grant'          => 40,
		'Leave'          => 41,
		'Reserve'        => 42,
		'Dismiss'        => 43,
		'Lose'           => 44,
		'Cast'           => 45,
		//46
		'Griffinegg'     => 47,
		'Attack'         => 48,
		'Recruit'        => 49,
		'EFFECT_MIDDLE'  => 50,
		'EVENT_MIDDLE'   => 51,
		'Siege'          => 52,
		'Transport'      => 53,
		'Smash'          => 54,
		'Operate'        => 55,
		//56
		'Spy'            => 57,
		//58
		'Sell'           => 59,
		'Buy'            => 60,
		'Construction'   => 61,
		'Vessel'         => 62,
		"Road"           => 63,
		'Commodity'      => 64,
		'Herb'           => 65,
		'RawMaterial'    => 66,
		//67
		'Unknown'        => 68,
		//69
		'Tax'            => 70,
        'Entertain'      => 71,
		'Steal'          => 72,
		'Explore'        => 73,
		//74
		'Unicum'         => 75,
		'Write'          => 76,
		'Devastate'      => 77,
		//78
		'Travel'         => 79,
		'Route'          => 80,
		'Follow'         => 81,
		//82
		'Teach'          => 83,
		'Learn'          => 84,
		//85
		'Offer'          => 86,
		'Demand'         => 87,
		'Amount'         => 88,
		'Price'          => 89,
		//90
		'Guard'          => 91,
		'Sort'           => 92,
		'Number'         => 93,
		'Comment'        => 94,
		'Migrate'        => 95,
		//96
		'Initiate'       => 97,
		'EFFECT_AFTER'   => 98,
		'EVENT_AFTER'    => 99
	];

	/**
	 * Priority of A-Effects.
	 */
	public final const AFTER_EFFECT = self::A_ACTION - 1;

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

	public function canShuffle(int $priority): bool {
		return match($priority) {
			self::B_ACTION - 1, self::B_ACTION,
			self::M_ACTION - 1, self::M_ACTION,
			self::A_ACTION - 1, self::A_ACTION => false,
			default                            => true
		};
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
			Priority::Before => self::B_ACTION,
			Priority::Middle => self::M_ACTION,
			Priority::After  => self::A_ACTION
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
