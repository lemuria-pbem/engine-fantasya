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
	 *
	 * @type array<string, int>
	 */
	public final const array ORDER = [
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
		'BattleSpell'    => 29,
		'Help'           => 20,
		'Contact'        => 21,
		'Quest'          => 22,
		'Visit'          => 23,
		'Announcement'   => 24,
		//25
		'Read'           => 26,
		'Take'           => 27,
		'Apply'          => 28,
		'Excert'         => 29,
		'Bestow'         => 30,
		'Give'           => 31,
		//32
		'Repeat'         => 33,
		'Accept'         => 34,
		'Cancel'         => 35,
		'Fee'            => 36,
		'Forbid'         => 37,
		'Allow'          => 38,
		'Quota'          => 39,
		'ThrowOut'       => 40,
		'Abandon'        => 41,
		'Enter'          => 42,
		'Board'          => 43,
		'Grant'          => 44,
		'Leave'          => 45,
		'Reserve'        => 46,
		'Dismiss'        => 47,
		'Lose'           => 48,
		//49
		'Cast'           => 50,
		'Griffinegg'     => 51,
		'Attack'         => 52,
		'Transport'      => 53,
		'Recruit'        => 54,
		'EFFECT_MIDDLE'  => 55,
		'EVENT_MIDDLE'   => 56,
		'Siege'          => 57,
		'Smash'          => 58,
		'Operate'        => 59,
		'Spy'            => 60,
		//61
		'Sell'           => 62,
		'Buy'            => 63,
		//64
		'Construction'   => 65,
		'Vessel'         => 66,
		"Road"           => 67,
		'Commodity'      => 68,
		'Herb'           => 69,
		'RawMaterial'    => 70,
		'Unknown'        => 71,
		'Tax'            => 72,
        'Entertain'      => 73,
		'Steal'          => 74,
		'Explore'        => 75,
		//76
		'Unicum'         => 77,
		'Write'          => 78,
		'Devastate'      => 79,
		//80
		'Travel'         => 81,
		'Route'          => 82,
		'Follow'         => 83,
		//84
		'Teach'          => 85,
		'Learn'          => 86,
		'Offer'          => 87,
		'Demand'         => 88,
		'Amount'         => 89,
		'Price'          => 90,
		'Forget'         => 91,
		'Guard'          => 92,
		'Sort'           => 93,
		'Number'         => 94,
		'Comment'        => 95,
		'Migrate'        => 96,
		'Initiate'       => 97,
		'EFFECT_AFTER'   => 98,
		'EVENT_AFTER'    => 99
	];

	/**
	 * Priority of A-Effects.
	 */
	public final const int AFTER_EFFECT = self::A_ACTION - 1;

	/**
	 * Priority of B-Events.
	 */
	private const int B_ACTION = 2;

	/**
	 * Priority of M-Events.
	 */
	private const int M_ACTION = 56;

	/**
	 * Priority of A-Events.
	 */
	private const int A_ACTION = 99;

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
