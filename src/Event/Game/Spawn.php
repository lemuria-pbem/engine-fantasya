<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Event\Game;

use Lemuria\Engine\Fantasya\Event\AbstractEvent;
use Lemuria\Engine\Fantasya\Event\Act\Create;
use Lemuria\Engine\Fantasya\Factory\OptionsTrait;
use Lemuria\Engine\Fantasya\Priority;
use Lemuria\Engine\Fantasya\State;
use Lemuria\Exception\LemuriaException;
use Lemuria\Id;
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

	public final const TYPE = 'type';

	public final const PARTY = 'party';

	public final const REGION = 'region';

	public final const RACE = 'race';

	public final const SIZE = 'size';

	public final const ZOMBIES = 'z';

	private const PARTY_ID = [Type::NPC->value => 'n', Type::Monster->value => 'm'];

	private Create $create;

	public static function getPartyId(Type $type):Id {
		$id = self::PARTY_ID[$type->value] ?? null;
		if (!$id) {
			throw new LemuriaException('Unsupported party type given.');
		}
		return Id::fromId($id);
	}

	public function __construct(State $state) {
		parent::__construct($state, Priority::AFTER);
	}

	public function setOptions(array $options): Spawn {
		$this->options = $options;
		return $this;
	}

	protected function initialize(): void {
		if ($this->hasOption(self::PARTY)) {
			$party = Party::get(Id::fromId($this->getOption(self::PARTY, 'string')));
		} else {
			/** @var Type $type */
			$type = $this->getOption(self::TYPE, Type::class);
			$party = Party::get(self::getPartyId($type));
		}
		$race   = $this->getOption(self::RACE, 'string');
		$size   = $this->getOption(self::SIZE, 'int');
		$region = Region::get(new Id($this->getOption(self::REGION, 'int')));
		$this->create = new Create($party, $region);
		$this->create->add(new Gang(self::createRace($race), $size));
	}

	protected function run(): void {
		$this->create->act();
	}
}
