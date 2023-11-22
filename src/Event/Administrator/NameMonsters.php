<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Event\Administrator;

use Lemuria\Engine\Fantasya\Event\AbstractEvent;
use Lemuria\Engine\Fantasya\Factory\Namer\RaceNamer;
use Lemuria\Engine\Fantasya\Priority;
use Lemuria\Engine\Fantasya\State;
use Lemuria\Exception\LemuriaException;
use Lemuria\Factory\Namer;
use Lemuria\Id;
use Lemuria\Model\Fantasya\Party;
use Lemuria\Model\Fantasya\Party\Type;

/**
 * This event fixes empty names of monsters.
 */
final class NameMonsters extends AbstractEvent
{
	private const string PARTY = 'm';

	private Namer $namer;

	public function __construct(State $state) {
		parent::__construct($state, Priority::Before);
		$this->namer = new RaceNamer();
	}

	protected function run(): void {
		$party = Party::get(Id::fromId(self::PARTY));
		if ($party->Type() !== Type::Monster) {
			throw new LemuriaException('Unexpected party type for Monster party.');
		}

		foreach ($party->People() as $unit) {
			if (empty($unit->Name())) {
				$this->namer->name($unit);
			}
		}
	}
}
