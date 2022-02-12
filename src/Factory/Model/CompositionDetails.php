<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Factory\Model;

use JetBrains\PhpStorm\Pure;

use function Lemuria\getClass;
use Lemuria\Model\Dictionary;
use Lemuria\Model\Fantasya\Composition;
use Lemuria\Model\Fantasya\Exception\JsonException;
use Lemuria\Model\Fantasya\Readable;
use Lemuria\Model\Fantasya\Storage\JsonProvider;
use Lemuria\Model\Fantasya\Unicum;
use Lemuria\SerializableTrait;

class CompositionDetails
{
	use SerializableTrait;

	protected final const DESCRIPTION = 'description';

	protected final const APPLY = 'apply';

	protected final const WRITE = 'write';

	protected static ?JsonProvider $provider = null;

	protected static ?Dictionary $dictionary = null;

	protected readonly string $file;

	protected array $json;

	/**
	 * @throws JsonException
	 */
	public function __construct(protected readonly Unicum $unicum) {
		if (!self::$provider) {
			self::$provider = new JsonProvider(__DIR__ . '/../../../resources/composition');
		}
		if (!self::$dictionary) {
			self::$dictionary = new Dictionary();
		}
		$this->file = getClass($this->Composition()) . '.json';
		$this->json = self::$provider->read($this->file);
		$this->validateJson();
	}

	#[Pure] public function Composition(): Composition {
		return $this->unicum->Composition();
	}

	#[Pure] public function Name(): string {
		return self::$dictionary->get('composition', $this->Composition());
	}

	/**
	 * @return string{]
	 */
	public function Description(): array {
		return $this->json[self::DESCRIPTION];
	}

	#[Pure] public function ApplyCommand(): string {
		$command = 'BENUTZEN [' . $this->Name() . '] Nummer';
		$apply   = $this->json[self::APPLY];
		if (strlen($apply) > 0) {
			$command .= ' ' . $apply;
		}
		return $command;
	}

	#[Pure] public function BestowCommand(): string {
		return 'GEBEN Einheit [' . $this->Name() . '] Nummer';
	}

	#[Pure] public function ReadCommand(): string {
		$command = $this->Composition() instanceof Readable ? 'LESEN' : 'UNTERSUCHEN';
		return $command . ' [' . $this->Name() . '] Nummer';
	}

	#[Pure] public function WriteCommand(): string {
		$command = 'SCHREIBEN [' . $this->Name() . '] Nummer';
		$write   = $this->json[self::WRITE];
		if (strlen($write) > 0) {
			$command .= ' ' . $write;
		}
		return $command;
	}

	protected function validateJson(): void {
		$this->validate($this->json, self::APPLY, 'string');
		$this->validate($this->json, self::WRITE, 'string');
	}
}
