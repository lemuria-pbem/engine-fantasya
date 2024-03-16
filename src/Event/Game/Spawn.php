<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Event\Game;

use Lemuria\Engine\Fantasya\Event\AbstractEvent;
use Lemuria\Engine\Fantasya\Event\Act\Create;
use Lemuria\Engine\Fantasya\Factory\OptionsTrait;
use Lemuria\Engine\Fantasya\Priority;
use Lemuria\Engine\Fantasya\State;
use Lemuria\Model\Fantasya\Factory\BuilderTrait;
use Lemuria\Model\Fantasya\Gang;
use Lemuria\Model\Fantasya\Party;
use Lemuria\Model\Fantasya\Party\Type;
use Lemuria\Model\Fantasya\Region;

/**
 * This event gives birth to a new NPC or monster unit.
 */
final class Spawn extends AbstractEvent
{
	use BuilderTrait;
	use OptionsTrait;

	public final const string TYPE = 'type';

	public final const string PARTY = 'party';

	public final const string REGION = 'region';

	public final const string RACE = 'race';

	public final const string SIZE = 'size';

	private Create $create;

	public function __construct(State $state) {
		parent::__construct($state, Priority::After);
	}

	public function setOptions(array $options): Spawn {
		$this->options = $options;
		return $this;
	}

	protected function initialize(): void {
		$race = self::createRace($this->getOption(self::RACE, 'string'));
		if ($this->hasOption(self::PARTY)) {
			$party = Party::get($this->getIdOption(self::PARTY));
		} elseif ($this->hasOption(self::TYPE)) {
			/** @var Type $type */
			$type  = $this->getOption(self::TYPE, Type::class);
			$party = $this->state->getTurnOptions()->Finder()->Party()->findByType($type);
		} else {
			$party = $this->state->getTurnOptions()->Finder()->Party()->findByRace($race);
		}
		$size   = $this->getOption(self::SIZE, 'int');
		$region = Region::get($this->getIdOption(self::REGION));
		$this->create = new Create($party, $region);
		$this->create->add(new Gang($race, $size));
	}

	protected function run(): void {
		$this->create->act();
	}
}
