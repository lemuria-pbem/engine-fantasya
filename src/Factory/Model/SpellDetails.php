<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Factory\Model;

use function Lemuria\getClass;
use Lemuria\Engine\Fantasya\Factory\SpellParser;
use Lemuria\Model\Dictionary;
use Lemuria\Model\Fantasya\BattleSpell;
use Lemuria\Model\Fantasya\Combat\Phase;
use Lemuria\Model\Fantasya\Exception\JsonException;
use Lemuria\Model\Fantasya\Spell;
use Lemuria\Model\Fantasya\Storage\JsonProvider;
use Lemuria\SerializableTrait;
use Lemuria\Validate;

class SpellDetails
{
	use SerializableTrait;

	protected final const DESCRIPTION = 'description';

	protected final const COMPONENTS = 'components';

	protected final const AURA = 'aura';

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

	public function Name(): string {
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
		return $this->json[self::COMPONENTS] ?? [];
	}

	public function Aura(): ?string {
		return $this->json[self::AURA] ?? null;
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
		$this->validate($this->json, self::DESCRIPTION, Validate::Array);
		$this->validateIfExists($this->json, self::COMPONENTS, Validate::Array);
		$this->validateIfExists($this->json, self::AURA, Validate::String);
	}

	protected function getParameters(): string {
		return match (SpellParser::getSyntax($this->spell)) {
			SpellParser::LEVEL             => '[Stufe]',
			SpellParser::TARGET            => 'Ziel',
			SpellParser::REGION            => '[Region]',
			SpellParser::DIRECTIONS        => 'Richtung [Richtung ...]',
			SpellParser::LEVEL_AND_TARGET  => '[Stufe] Ziel',
			SpellParser::DOMAIN_AND_TARGET => '[Burg | Gebäude | Region | Schiff] Nummer',
			default                        => ''
		};
	}
}
