<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Event\Game;

use Lemuria\Engine\Fantasya\Action;
use Lemuria\Engine\Fantasya\Event\AbstractEvent;
use Lemuria\Engine\Fantasya\Event\Act\Create;
use Lemuria\Engine\Fantasya\Factory\OptionsTrait;
use Lemuria\Engine\Fantasya\State;
use Lemuria\Id;
use Lemuria\Model\Fantasya\Factory\BuilderTrait;
use Lemuria\Model\Fantasya\Gang;
use Lemuria\Model\Fantasya\Party;
use Lemuria\Model\Fantasya\Region;

/**
 * This event gives birth to a new NPC or monster unit.
 */
final class Spawn extends AbstractEvent
{
	use BuilderTrait;
	use OptionsTrait;

	public const PARTY = 'party';

	public const REGION = 'region';

	public const RACE = 'race';

	public const SIZE = 'size';

	public const PARTY_ID = [Party::NPC => 'n', Party::MONSTER => 'm'];

	private Create $create;

	public function __construct(State $state) {
		parent::__construct($state, Action::AFTER);
	}

	public function setOptions(array $options): Spawn {
		$this->options = $options;
		return $this;
	}

	protected function initialize(): void {
		$type = $this->getOption(self::PARTY, 'int');
		$race = $this->getOption(self::RACE, 'string');
		$size = $this->getOption(self::SIZE, 'int');
		if (!isset(self::PARTY_ID[$type])) {
			throw new \InvalidArgumentException($type);
		}

		$party  = Party::get(Id::fromId(self::PARTY_ID[$type]));
		$region = Region::get(new Id($this->getOption(self::REGION, 'int')));
		$this->create = new Create($party, $region);
		$this->create->add(new Gang(self::createRace($race), $size));
	}

	protected function run(): void {
		$this->create->act();
	}
}
