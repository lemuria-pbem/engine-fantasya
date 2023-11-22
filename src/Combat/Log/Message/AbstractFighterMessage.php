<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Combat\Log\Message;

use Lemuria\Validate;

abstract class AbstractFighterMessage extends AbstractMessage
{
	private const string FIGHTER = 'fighter';

	protected array $simpleParameters = [self::FIGHTER];

	public function __construct(protected ?string $fighter = null) {
		parent::__construct();
	}

	public function unserialize(array $data): static {
		parent::unserialize($data);
		$this->fighter = $data[self::FIGHTER];
		return $this;
	}

	protected function getParameters(): array {
		return [self::FIGHTER => $this->fighter];
	}

	protected function validateSerializedData(array $data): void {
		parent::validateSerializedData($data);
		$this->validate($data, self::FIGHTER, Validate::String);
	}
}
