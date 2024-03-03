<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Event\Game;

use Lemuria\Engine\Fantasya\Event\AbstractEvent;
use Lemuria\Engine\Fantasya\Exception\CommandException;
use Lemuria\Engine\Fantasya\Factory\OptionsTrait;
use Lemuria\Engine\Fantasya\Priority;
use Lemuria\Engine\Fantasya\State;
use Lemuria\Lemuria;
use Lemuria\Model\Fantasya\Factory\BuilderTrait;
use Lemuria\Model\Fantasya\Party\Type;
use Lemuria\Model\Fantasya\Region;
use Lemuria\Model\Fantasya\Unit;

/**
 * Transports a monster unit through astral space from one region to another.
 */
final class TransportMonster extends AbstractEvent
{
	use BuilderTrait;
	use OptionsTrait;

	public final const string UNIT = 'unit';

	public final const string REGION = 'region';

	private Unit $unit;

	private Region $region;

	public function __construct(State $state) {
		parent::__construct($state, Priority::After);
	}

	public function setOptions(array $options): TransportMonster {
		$this->options = $options;
		return $this;
	}

	protected function initialize(): void {
		$this->unit = Unit::get($this->getIdOption(self::UNIT));
		if ($this->unit->Party()->Type() !== Type::Monster) {
			throw new CommandException('Only monster units can be transported.');
		}
		if ($this->unit->Construction() || $this->unit->Vessel()) {
			throw new CommandException('Only monsters in the wild can be transported.');
		}
		$this->region = Region::get($this->getIdOption(self::REGION));
	}

	protected function run(): void {
		$region = $this->unit->Region();
		if ($region === $this->region) {
			Lemuria::Log()->debug('Monster ' . $this->unit . ' already is in ' . $region . '.');
		} else {
			$region->Residents()->remove($this->unit);
			$this->region->Residents()->add($this->unit);
			$this->unit->Party()->Chronicle()->add($this->region);
			Lemuria::Log()->debug('Monster ' . $this->unit . ' has been summoned to ' . $this->region . '.');
		}
	}
}
