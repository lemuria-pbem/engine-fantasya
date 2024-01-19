<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Effect;

use Lemuria\Engine\Fantasya\Priority;
use Lemuria\Engine\Fantasya\State;
use Lemuria\Exception\UnserializeEntityException;
use Lemuria\Lemuria;
use Lemuria\Model\Fantasya\Gathering;
use Lemuria\Model\Fantasya\Party\Type;
use Lemuria\Validate;

final class VisitEffect extends AbstractUnitEffect
{
	private const string EVERYBODY = 'everybody';

	private const string PARTIES = 'parties';

	private readonly Gathering $parties;

	private bool $everybody = false;

	public function __construct(State $state) {
		parent::__construct($state, Priority::Before);
		$this->parties = new Gathering();
	}

	public function Everybody(): bool {
		return $this->everybody;
	}

	public function Parties(): Gathering {
		return $this->parties;
	}

	public function serialize(): array {
		$data                  = parent::serialize();
		$data[self::EVERYBODY] = $this->everybody;
		$data[self::PARTIES]   = $this->parties->serialize();
		return $data;
	}

	public function unserialize(array $data): static {
		parent::unserialize($data);
		$this->everybody = $data[self::EVERYBODY];
		$this->parties->unserialize($data[self::PARTIES]);
		return $this;
	}

	public function setEverybody(bool $enable = true): VisitEffect {
		$this->everybody = $enable;
		return $this;
	}

	/**
	 * @throws UnserializeEntityException
	 */
	protected function validateSerializedData(array $data): void {
		parent::validateSerializedData($data);
		$this->validate($data, self::EVERYBODY, Validate::Bool);
		$this->validate($data, self::PARTIES, Validate::Array);
	}

	protected function run(): void {
		$this->everybody = false;
		$this->parties->clear();
		if ($this->Unit()->Party()->Type() !== Type::NPC) {
			Lemuria::Score()->remove($this);
		}
	}
}
