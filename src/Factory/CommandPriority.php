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
		'Origin'           => 5,
		'Banner'           => 6,
		'Presetting'       => 7,
		'Name'             => 8,
		'Describe'         => 9,
		'Rumor'            => 10,
		'Disguise'         => 11,
		'Gather'           => 12,
		'Loot'             => 13,
		'Unguard'          => 15,
		'Fee'              => 16,
		'Allow'            => 17,
		'Forbid'           => 18,
		'Fight'            => 19,
		'BattleSpell'      => 20,
		'Help'             => 21,
		'Contact'          => 22,
		'Visit'            => 23,
		'Announcement'     => 24,
		'Take'             => 26,
		'Read'             => 27,
		'Apply'            => 28,
		'Excert'           => 29,
		'Bestow'           => 30,
		'Give'             => 32,
		'Repeat'           => 33,
		'Accept'           => 34,
		'Cancel'           => 35,
		'Abandon'          => 36,
		'Enter'            => 37,
		'Board'            => 38,
		'Grant'            => 39,
		'Leave'            => 40,
		'Reserve'          => 41,
		'Dismiss'          => 42,
		'Lose'             => 43,
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
		'RawMaterial'      => 71,
		'Unknown'          => 73,
		'Tax'              => 75,
        'Entertain'        => 76,
		'Steal'            => 77,
		'Explore'          => 79,
		'Unicum'           => 80,
		'Write'            => 81,
		'Devastate'        => 82,
		'Travel'           => 84,
		'Route'            => 85,
		'Follow'           => 86,
		'Offer'            => 88,
		'Demand'           => 89,
		'Guard'            => 90,
		'Sort'             => 92,
		'Number'           => 93,
		'Comment'          => 94,
		'Migrate'          => 96,
		'Initiate'         => 97,
		'EFFECT_AFTER'     => 98,
		'EVENT_AFTER'      => 99
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
