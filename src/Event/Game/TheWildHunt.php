<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Event\Game;

use Lemuria\Engine\Fantasya\Command\Apply\GoliathWater;
use Lemuria\Engine\Fantasya\Command\Apply\SevenLeagueTea;
use Lemuria\Engine\Fantasya\Event\AbstractEvent;
use Lemuria\Engine\Fantasya\Factory\OptionsTrait;
use Lemuria\Engine\Fantasya\Message\Party\TheWildHuntMessage;
use Lemuria\Engine\Fantasya\Priority;
use Lemuria\Engine\Fantasya\State;
use Lemuria\Model\Fantasya\Commodity\Gold;
use Lemuria\Model\Fantasya\Commodity\Herb\Peyote;
use Lemuria\Model\Fantasya\Commodity\Herb\Sandreeker;
use Lemuria\Model\Fantasya\Commodity\Herb\Snowcrystal;
use Lemuria\Model\Fantasya\Commodity\Luxury\Gem;
use Lemuria\Model\Fantasya\Commodity\Luxury\Myrrh;
use Lemuria\Model\Fantasya\Commodity\Luxury\Olibanum;
use Lemuria\Model\Fantasya\Commodity\Potion\Brainpower;
use Lemuria\Model\Fantasya\Commodity\Potion\ElixirOfPower;
use Lemuria\Model\Fantasya\Commodity\Potion\HealingPotion;
use Lemuria\Model\Fantasya\Commodity\Potion\Woundshut;
use Lemuria\Model\Fantasya\Factory\BuilderTrait;
use Lemuria\Model\Fantasya\Quantity;
use Lemuria\Model\Fantasya\Unit;

/**
 * In the Wild Hunt all parties receive a bunch of gifts.
 */
final class TheWildHunt extends AbstractEvent
{
	use BuilderTrait;
	use OptionsTrait;

	public final const string UNIT = 'unit';

	/**
	 * @type array<string, int>
	 */
	private const array GIFTS = [
		Gold::class       => 1,
		Gem::class        => 10, Myrrh::class         => 10, Olibanum::class     => 10,
		Peyote::class     => 3, Sandreeker::class     => 3, Snowcrystal::class   => 3,
		Brainpower::class => 3, HealingPotion::class  => 2, ElixirOfPower::class => 1,
		Woundshut::class  => 1, SevenLeagueTea::class => 2, GoliathWater::class  => 1
	];

	private Unit $unit;

	public function __construct(State $state) {
		parent::__construct($state, Priority::Before);
	}

	public function setOptions(array $options): TheWildHunt {
		$this->options = $options;
		return $this;
	}

	protected function initialize(): void {
		$this->unit = Unit::get($this->getIdOption(self::UNIT));
	}

	protected function run(): void {
		foreach (self::GIFTS as $class => $amount) {
			$gift = new Quantity(self::createCommodity($class), $amount);
			$this->unit->Inventory()->add($gift);
		}
		$this->message(TheWildHuntMessage::class, $this->unit->Party())->e($this->unit)->p($this->unit->Region()->Name());
	}
}
