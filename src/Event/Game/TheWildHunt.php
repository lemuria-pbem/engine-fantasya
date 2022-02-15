<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Event\Game;

use Lemuria\Engine\Fantasya\Event\AbstractEvent;
use Lemuria\Engine\Fantasya\Factory\OptionsTrait;
use Lemuria\Engine\Fantasya\Message\Party\TheWildHuntMessage;
use Lemuria\Engine\Fantasya\Priority;
use Lemuria\Engine\Fantasya\State;
use Lemuria\Id;
use Lemuria\Model\Fantasya\Commodity\Herb\Peyote;
use Lemuria\Model\Fantasya\Commodity\Herb\Sandreeker;
use Lemuria\Model\Fantasya\Commodity\Herb\Snowcrystal;
use Lemuria\Model\Fantasya\Commodity\Luxury\Gem;
use Lemuria\Model\Fantasya\Commodity\Luxury\Myrrh;
use Lemuria\Model\Fantasya\Commodity\Luxury\Olibanum;
use Lemuria\Model\Fantasya\Commodity\Potion\Brainpower;
use Lemuria\Model\Fantasya\Commodity\Potion\ElixirOfPower;
use Lemuria\Model\Fantasya\Commodity\Potion\HealingPotion;
use Lemuria\Model\Fantasya\Factory\BuilderTrait;
use Lemuria\Model\Fantasya\Quantity;
use Lemuria\Model\Fantasya\Unit;

/**
 * A travelling sorcerer gifts a potion.
 */
final class TheWildHunt extends AbstractEvent
{
	use BuilderTrait;
	use OptionsTrait;

	public const UNIT = 'unit';

	private const GIFTS = [
		Gem::class        => 5, Myrrh::class         => 5, Olibanum::class      => 5,
		Peyote::class     => 3, Sandreeker::class    => 3, Snowcrystal::class   => 3,
		Brainpower::class => 3, HealingPotion::class => 2, ElixirOfPower::class => 1
	];

	private Unit $unit;

	public function __construct(State $state) {
		parent::__construct($state, Priority::BEFORE);
	}

	public function setOptions(array $options): TheWildHunt {
		$this->options = $options;
		return $this;
	}

	protected function initialize(): void {
		$this->unit = Unit::get(new Id($this->getOption(self::UNIT, 'int')));
	}

	protected function run(): void {
		foreach (self::GIFTS as $class => $amount) {
			$gift = new Quantity(self::createCommodity($class), $amount);
			$this->unit->Inventory()->add($gift);
		}
		$this->message(TheWildHuntMessage::class, $this->unit->Party())->e($this->unit)->p($this->unit->Region()->Name());
	}
}
