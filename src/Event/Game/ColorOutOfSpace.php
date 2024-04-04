<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Event\Game;

use Lemuria\Engine\Fantasya\Effect\ColorOutOfSpaceEffect;
use Lemuria\Engine\Fantasya\Event\AbstractEvent;
use Lemuria\Engine\Fantasya\Factory\OptionsTrait;
use Lemuria\Engine\Fantasya\Priority;
use Lemuria\Engine\Fantasya\State;
use Lemuria\Lemuria;
use Lemuria\Model\Fantasya\Region;

/**
 * A dark ritual is performed over multiple weeks to summon The Color Out Of Space into a well or lake.
 */
final class ColorOutOfSpace extends AbstractEvent
{
	use OptionsTrait;

	public final const string MOUNTAIN = 'mountain';

	public final const string REGION = 'region';

	public final const string ROUNDS = 'rounds';

	private const int DEFAULT_ROUNDS = 2;

	private Region $mountain;

	private Region $region;

	private int $rounds = self::DEFAULT_ROUNDS;

	public function __construct(State $state) {
		parent::__construct($state, Priority::Before);
	}

	public function setOptions(array $options): ColorOutOfSpace {
		$this->options = $options;
		return $this;
	}

	protected function initialize(): void {
		$this->mountain = Region::get($this->getIdOption(self::MOUNTAIN));
		$this->region   = Region::get($this->getIdOption(self::REGION));
		if ($this->hasOption(self::ROUNDS)) {
			$this->rounds = $this->getOption(self::ROUNDS, 'int');
		}
	}

	protected function run(): void {
		$effect = new ColorOutOfSpaceEffect(State::getInstance());
		if (!Lemuria::Score()->find($effect->setRegion($this->mountain))) {
			$effect->setTarget($this->region)->setRounds($this->rounds);
			Lemuria::Log()->debug('The Colour Out Of Space ritual has been initiated.');
			Lemuria::Score()->add($effect->startTheRitual());
		}
	}
}
