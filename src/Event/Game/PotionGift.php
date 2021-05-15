<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Event\Game;

use JetBrains\PhpStorm\Pure;

use Lemuria\Engine\Fantasya\Action;
use Lemuria\Engine\Fantasya\Event\AbstractEvent;
use Lemuria\Engine\Fantasya\Exception\UnknownItemException;
use Lemuria\Engine\Fantasya\Factory\OptionsTrait;
use Lemuria\Engine\Fantasya\Message\Party\PotionGiftMessage;
use Lemuria\Engine\Fantasya\State;
use Lemuria\Id;
use Lemuria\Model\Fantasya\Factory\BuilderTrait;
use Lemuria\Model\Fantasya\Potion;
use Lemuria\Model\Fantasya\Quantity;
use Lemuria\Model\Fantasya\Unit;

/**
 * A travelling sorcerer gifts a potion.
 */
final class PotionGift extends AbstractEvent
{
	use BuilderTrait;
	use OptionsTrait;

	public const UNIT = 'unit';

	public const POTION = 'potion';

	private Unit $unit;

	private Potion $potion;

	#[Pure] public function __construct(State $state) {
		parent::__construct($state, Action::BEFORE);
	}

	public function setOptions(array $options): PotionGift {
		$this->options = $options;
		return $this;
	}

	protected function initialize(): void {
		$this->unit  = Unit::get(new Id($this->getOption(self::UNIT, 'int')));
		$potionClass = $this->getOption(self::POTION, 'string');
		$potion      = self::createCommodity($potionClass);
		if ($potion instanceof Potion) {
			$this->potion = $potion;
		} else {
			throw new UnknownItemException($potion);
		}
	}

	protected function run(): void {
		$gift = new Quantity($this->potion);
		$this->unit->Inventory()->add($gift);
		$this->message(PotionGiftMessage::class, $this->unit->Party())->e($this->unit)->i($gift);
	}
}
