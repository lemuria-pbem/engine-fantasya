<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Event\Game;

use Lemuria\Engine\Fantasya\Effect\UnicumDisintegrate;
use Lemuria\Engine\Fantasya\Event\AbstractEvent;
use Lemuria\Engine\Fantasya\Factory\OptionsTrait;
use Lemuria\Engine\Fantasya\Priority;
use Lemuria\Engine\Fantasya\State;
use Lemuria\Id;
use Lemuria\Lemuria;
use Lemuria\Model\Dictionary;
use Lemuria\Model\Domain;
use Lemuria\Model\Fantasya\Composition\Scroll;
use Lemuria\Model\Fantasya\Factory\BuilderTrait;
use Lemuria\Model\Fantasya\Region;
use Lemuria\Model\Fantasya\Spell;
use Lemuria\Model\Fantasya\Unicum;

/**
 * A Scroll unicum has been blown away to a specific region, where it can be picket up.
 */
final class BlownByTheWind extends AbstractEvent
{
	use BuilderTrait;
	use OptionsTrait;

	public final const REGION = 'region';

	public final const SPELL = 'spell';

	private const DESCRIPTION = 'Ein leicht zerknittertes und fleckiges, beschriebenes Pergamentblatt.';

	private const ROUNDS = 3;

	protected ?Dictionary $dictionary;

	private Region $region;

	private Spell $spell;

	public function __construct(State $state) {
		parent::__construct($state, Priority::After);
		$this->dictionary = new Dictionary();
	}

	public function setOptions(array $options): BlownByTheWind {
		$this->options = $options;
		return $this;
	}

	protected function initialize(): void {
		$this->region = Region::get(new Id($this->getOption(self::REGION, 'int')));
		$this->spell  = self::createSpell($this->getOption(self::SPELL, 'string'));
	}

	protected function run(): void {
		$unicum = new Unicum();
		$scroll = $this->createUnicum($unicum)->setSpell($this->spell);
		$this->region->Treasury()->add($unicum->setComposition($scroll));
		Lemuria::Log()->debug('A new ' . $scroll . ' containing ' . $this->spell . ' has been placed in ' . $this->region . '.');
		$effect = new UnicumDisintegrate($this->state);
		Lemuria::Score()->add($effect->setUnicum($unicum)->setRounds(self::ROUNDS));
	}

	private function createUnicum(Unicum $unicum): Scroll {
		$unicum->setId(Lemuria::Catalog()->nextId(Domain::Unicum));
		$unicum->setName($this->dictionary->get('spell', $this->spell));
		$unicum->setDescription(self::DESCRIPTION);
		/** @var Scroll $scroll */
		$scroll = self::createComposition(Scroll::class);
		return $scroll;
	}
}
