<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Factory\Model;

use JetBrains\PhpStorm\Pure;

use function Lemuria\getClass;
use Lemuria\Engine\Fantasya\Factory\SpellParser;
use Lemuria\Model\Dictionary;
use Lemuria\Model\Fantasya\BattleSpell;
use Lemuria\Model\Fantasya\Combat\Phase;
use Lemuria\Model\Fantasya\Exception\JsonException;
use Lemuria\Model\Fantasya\Spell;
use Lemuria\Model\Fantasya\Storage\JsonProvider;
use Lemuria\SerializableTrait;

class SpellDetails
{
	use SerializableTrait;

	protected final const DESCRIPTION = 'description';

	protected final const COMPONENTS = 'components';

	protected static ?JsonProvider $provider = null;

	protected static ?Dictionary $dictionary = null;

	protected readonly string $file;

	protected array $json;

	/**
	 * @throws JsonException
	 */
	public function __construct(protected readonly Spell $spell) {
		if (!self::$provider) {
			self::$provider = new JsonProvider(__DIR__ . '/../../../resources/spell');
		}
		if (!self::$dictionary) {
			self::$dictionary = new Dictionary();
		}
		$this->file = getClass($this->spell) . '.json';
		$this->json = self::$provider->read($this->file);
		$this->validateJson();
	}

	public function Spell(): Spell {
		return $this->spell;
	}

	/**
	 * @noinspection PhpPureFunctionMayProduceSideEffectsInspection
	 */
	#[Pure] public function Name(): string {
		return self::$dictionary->get('spell', $this->spell);
	}

	/**
	 * @return string{]
	 */
	public function Description(): array {
		return $this->json[self::DESCRIPTION];
	}

	/**
	 * @return string[]
	 */
	public function Components(): array {
		return $this->json[self::COMPONENTS];
	}

	public function IsBattleSpell(): bool {
		return $this->spell instanceof BattleSpell;
	}

	public function CombatPhase(): string {
		if ($this->spell instanceof BattleSpell) {
			return match ($this->spell->Phase()) {
				Phase::PREPARATION => 'Vorbereitung',
				Phase::COMBAT      => 'Angriff'
			};
		}
		return '';
	}

	public function Syntax(): string {
		$verb       = $this->IsBattleSpell() ? 'KAMPFZAUBER' : 'ZAUBERN';
		$parameters = $this->getParameters();
		$syntax     = $verb . ' ' . $this->Name();
		if ($parameters) {
			$syntax .= ' ' . $parameters;
		}
		return $syntax;
	}

	protected function validateJson(): void {
		$this->validate($this->json, self::DESCRIPTION, 'array');
		$this->validate($this->json, self::COMPONENTS, 'array');
	}

	protected function getParameters(): string {
		return match (SpellParser::getSyntax($this->spell)) {
			SpellParser::LEVEL            => '[Stufe]',
			SpellParser::TARGET           => 'Ziel',
			SpellParser::REGION           => '[Region]',
			SpellParser::LEVEL_AND_TARGET => '[Stufe] Ziel',
			default                       => ''
		};
	}
}
