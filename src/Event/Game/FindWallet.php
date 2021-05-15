<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Event\Game;

use JetBrains\PhpStorm\Pure;

use Lemuria\Engine\Fantasya\Action;
use Lemuria\Engine\Fantasya\Event\AbstractEvent;
use Lemuria\Engine\Fantasya\Factory\OptionsTrait;
use Lemuria\Engine\Fantasya\Message\Party\FindWalletMessage;
use Lemuria\Engine\Fantasya\State;
use Lemuria\Id;
use Lemuria\Model\Fantasya\Commodity\Silver;
use Lemuria\Model\Fantasya\Factory\BuilderTrait;
use Lemuria\Model\Fantasya\Quantity;
use Lemuria\Model\Fantasya\Unit;

/**
 * A unit finds a lost wallet containing a good amount of silver.
 */
final class FindWallet extends AbstractEvent
{
	use BuilderTrait;
	use OptionsTrait;

	public const UNIT = 'unit';

	public const SILVER = 'silver';

	private Unit $unit;

	private Quantity $silver;

	#[Pure] public function __construct(State $state) {
		parent::__construct($state, Action::BEFORE);
	}

	public function setOptions(array $options): FindWallet {
		$this->options = $options;
		return $this;
	}

	protected function initialize(): void {
		$this->unit   = Unit::get(new Id($this->getOption(self::UNIT, 'int')));
		$silver       = self::createCommodity(Silver::class);
		$this->silver = new Quantity($silver, $this->getOption(self::SILVER, 'int'));
	}

	protected function run(): void {
		$this->unit->Inventory()->add($this->silver);
		$this->message(FindWalletMessage::class, $this->unit->Party())->e($this->unit)->i($this->silver);
	}
}
