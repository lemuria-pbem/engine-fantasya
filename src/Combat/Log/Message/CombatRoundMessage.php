<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Combat\Log\Message;

use Lemuria\Validate;

class CombatRoundMessage extends AbstractMessage
{
	private const ROUND = 'round';

	protected array $simpleParameters = [self::ROUND];

	public function __construct(protected ?int $round = null) {
		parent::__construct();
	}

	public function getDebug(): string {
		return 'Combat round ' . $this->round . ' starts.';
	}

	public function unserialize(array $data): static {
		parent::unserialize($data);
		$this->round = $data[self::ROUND];
		return $this;
	}

	protected function getParameters(): array {
		return [self::ROUND => $this->round];
	}

	protected function validateSerializedData(array $data): void {
		parent::validateSerializedData($data);
		$this->validate($data, self::ROUND, Validate::Int);
	}
}
