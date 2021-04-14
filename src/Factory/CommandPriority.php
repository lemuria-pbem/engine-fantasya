<?php
declare (strict_types = 1);
namespace Lemuria\Engine\Fantasya\Factory;

use function Lemuria\getClass;
use Lemuria\Engine\Fantasya\Action;
use Lemuria\Engine\Fantasya\Command;
use Lemuria\Engine\Fantasya\Effect;
use Lemuria\Engine\Fantasya\Event;
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
		'EFFECT_BEFORE'  => 1,
		'EVENT_BEFORE'   => 2,
		'DefaultCommand' => 4,
		'Banner'         => 6,
		'Name'           => 7,
		'Describe'       => 8,
		'Disguise'       => 10,
		'Unguard'        => 12,
		'Origin'         => 14,
		'Fight'          => 16,
		'Help'           => 19,
		'Contact'        => 22,
		'Announcement'   => 25,
		'Enter'          => 28,
		'Board'          => 29,
		'Grant'          => 31,
		'Leave'          => 33,
		'Reserve'        => 36,
		'Give'           => 40,
		'Dismiss'        => 41,
		'Lose'           => 42,
		// ATTACKIERE => 45,
		'Recruit'        => 48,
		'EFFECT_MIDDLE'  => 50,
		'EVENT_MIDDLE'   => 51,
		// BELAGERE => 55,
		// ZAUBERE => 58,
		'Smash'          => 60,
		'Travel'         => 63,
		// ROUTE => 64,
		'Teach'          => 67,
		'Learn'          => 69,
		// SPIONIERE => 72,
		'Sell'           => 74,
		'Buy'            => 75,
		'Construction'   => 77,
		'Vessel'         => 78,
		'Commodity'      => 80,
		'RawMaterial'    => 82,
		'Unknown'        => 84,
		'Tax'            => 86,
        'Entertain'      => 88,
		'Guard'          => 90,
		'Sort'           => 93,
		'Number'         => 94,
		'Comment'        => 95,
		'Migrate'        => 97,
		'EFFECT_AFTER'   => 98,
		'EVENT_AFTER'    => 99,
		'Initiate'       => 100
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

		$priority = $action->Priority();

		if ($action instanceof Event) {
			if ($priority <= Action::BEFORE) {
				return self::B_ACTION;
			}
			if ($priority >= Action::AFTER) {
				return self::A_ACTION;
			}
			return self::M_ACTION;
		}

		if ($action instanceof Effect) {
			if ($priority <= Action::BEFORE) {
				return self::B_ACTION - 1;
			}
			if ($priority >= Action::AFTER) {
				return self::A_ACTION - 1;
			}
			return self::M_ACTION - 1;
		}

		throw new LemuriaException('Unsupported action: ' . getClass($action));
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
