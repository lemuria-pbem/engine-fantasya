<?php
declare (strict_types = 1);
namespace Lemuria\Engine\Lemuria;

use function Lemuria\getClass;
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
		// B-Ereignisse => 1,
		// B-Effekte => 2,
		// DEFAULT => 4,
		'Name'         => 7,
		'Describe'     => 8,
		'Disguise'     => 10,
		'Unguard'      => 12,
		// URSPRUNG => 14,
		'Fight'        => 16,
		'Help'         => 20,
		'Contact'      => 22,
		// BOTSCHAFT => 25,
		'Enter'        => 28,
		'Board'        => 30,
		'Grant'        => 33,
		'Leave'        => 35,
		'Reserve'      => 38,
		'Give'         => 39,
		// ATTACKIERE => 42,
		// BELAGERE => 43,
		// ZAUBERE => 45,
		// NACH => 47,
		// ROUTE => 48,
		// M-Ereignisse => 50,
		// M-Effekte   => 51,
		'Recruit'      => 54,
		'Teach'        => 60,
		'Learn'        => 62,
		// SPIONIERE => 67,
		// VERKAUFE => 70,
		// KAUFE => 73,
		'Product'      => 78,
		'RawMaterial'  => 80,
		'CollectTaxes' => 84,
        'Entertain'    => 87,
		'Guard'        => 90,
		'Sort'         => 94,
		'Number'       => 95,
		'Comment'      => 96
		// A-Ereignisse => 98,
		// A-Effekte => 99,
		// 100 reserved for default
	];

	/**
	 * Priority of B-Events.
	 */
	private const B_ACTION = 1;

	/**
	 * Priority of M-Events.
	 */
	private const M_ACTION = 50;

	/**
	 * Priority of A-Events.
	 */
	private const A_ACTION = 98;

	/**
	 * The lowest possible execution priority.
	 */
	private const LOWEST = 100;

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
			return self::ORDER[$class] ?? self::LOWEST;
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
				return self::B_ACTION + 1;
			}
			if ($priority >= Action::AFTER) {
				return self::A_ACTION + 1;
			}
			return self::M_ACTION + 1;
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
