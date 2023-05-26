<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Factory\Model;

use function Lemuria\getClass;
use Lemuria\Engine\Fantasya\Factory\GrammarTrait;
use Lemuria\Engine\Fantasya\Message\Casus;
use Lemuria\Model\Fantasya\Composition;
use Lemuria\Model\Fantasya\Exception\JsonException;
use Lemuria\Model\Fantasya\Readable;
use Lemuria\Model\Fantasya\Storage\JsonProvider;
use Lemuria\Model\Fantasya\Unicum;
use Lemuria\SerializableTrait;
use Lemuria\Validate;

class CompositionDetails
{
	use GrammarTrait;
	use SerializableTrait;

	protected final const DESCRIPTION = 'description';

	protected final const APPLY = 'apply';

	protected final const TAKE = 'take';

	protected final const WRITE = 'write';

	protected static ?JsonProvider $provider = null;

	protected readonly string $file;

	protected array $json;

	/**
	 * @throws JsonException
	 */
	public function __construct(protected readonly Unicum $unicum) {
		if (!self::$provider) {
			self::$provider = new JsonProvider(__DIR__ . '/../../../resources/composition');
		}
		$this->file = getClass($this->Composition()) . '.json';
		$this->json = self::$provider->read($this->file);
		$this->validateJson();
	}

	public function Composition(): Composition {
		return $this->unicum->Composition();
	}

	public function Name(): string {
		return $this->translateSingleton($this->Composition(), casus: Casus::Nominative);
	}

	/**
	 * @return string{]
	 */
	public function Description(): array {
		return $this->json[self::DESCRIPTION];
	}

	public function ApplyCommand(): string {
		$command = 'BENUTZEN [' . $this->Name() . '] Nummer';
		$apply   = $this->json[self::APPLY];
		if (strlen($apply) > 0) {
			$command .= ' ' . $apply;
		}
		return $command;
	}

	public function BestowCommand(): string {
		return 'GEBEN Einheit [' . $this->Name() . '] Nummer';
	}

	public function DestroyCommand(): string {
		return 'VERNICHTEN [' . $this->Name() . '] Nummer';
	}

	public function LoseCommand(): string {
		return 'VERLIEREN [' . $this->Name() . '] Nummer';
	}

	public function ReadCommand(): string {
		$command = $this->Composition() instanceof Readable ? 'LESEN' : 'UNTERSUCHEN';
		return $command . ' [' . $this->Name() . '] Nummer';
	}

	public function TakeCommand(): string {
		$command = 'NEHMEN [' . $this->Name() . '] Nummer';
		$take    = $this->json[self::TAKE] ?? '';
		if (strlen($take) > 0) {
			$command .= ' ' . $take;
		}
		return $command;
	}

	public function WriteCommand(): string {
		$command = 'SCHREIBEN [' . $this->Name() . '] Nummer';
		$write   = $this->json[self::WRITE];
		if (strlen($write) > 0) {
			$command .= ' ' . $write;
		}
		return $command;
	}

	protected function validateJson(): void {
		$this->validateIfExists($this->json, self::APPLY, Validate::String);
		$this->validateIfExists($this->json, self::TAKE, Validate::String);
		$this->validateIfExists($this->json, self::WRITE, Validate::String);
	}
}
