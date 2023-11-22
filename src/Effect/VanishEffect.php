<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Effect;

use Lemuria\Engine\Fantasya\Message\Unit\VanishEffectCreateMessage;
use Lemuria\Engine\Fantasya\Message\Unit\VanishEffectMessage;
use Lemuria\Engine\Fantasya\Priority;
use Lemuria\Engine\Fantasya\State;
use Lemuria\Exception\UnserializeEntityException;
use Lemuria\Id;
use Lemuria\Lemuria;
use Lemuria\Model\Fantasya\Unit;
use Lemuria\Validate;

final class VanishEffect extends AbstractUnitEffect
{
	private const string SUMMONER = 'summoner';

	private const string WEEKS = 'weeks';

	protected ?bool $isReassign = null;

	private ?int $summoner = null;

	private int $weeks = 1;

	public function __construct(State $state) {
		parent::__construct($state, Priority::After);
	}

	public function Summoner(): ?Unit {
		return is_int($this->summoner) ? Unit::get(new Id($this->summoner)) : null;
	}

	public function Weeks(): int {
		return $this->weeks;
	}

	public function serialize(): array {
		$data                 = parent::serialize();
		$data[self::SUMMONER] = $this->summoner;
		$data[self::WEEKS]    = $this->weeks;
		return $data;
	}

	public function unserialize(array $data): static {
		parent::unserialize($data);
		$this->summoner = $data[self::SUMMONER];
		$this->weeks    = $data[self::WEEKS];
		return $this;
	}

	public function setSummoner(?Unit $summoner): VanishEffect {
		$this->summoner = $summoner?->Id()->Id();
		return $this;
	}

	public function setWeeks(int $weeks): VanishEffect {
		$this->weeks = $weeks;
		$this->message(VanishEffectCreateMessage::class, $this->Unit())->p($weeks);
		return $this;
	}

	/**
	 * @throws UnserializeEntityException
	 */
	protected function validateSerializedData(array $data): void {
		parent::validateSerializedData($data);
		$this->validate($data, self::SUMMONER, Validate::IntOrNull);
		$this->validate($data, self::WEEKS, Validate::Int);
	}

	protected function run(): void {
		$this->weeks--;
		if ($this->weeks <= 0) {
			$unit = $this->Unit();
			$unit->Region()->Residents()->remove($unit);
			$unit->Party()->People()->remove($unit);
			Lemuria::Catalog()->reassign($unit);
			Lemuria::Catalog()->remove($unit);
			Lemuria::Score()->remove($this);
			$this->message(VanishEffectMessage::class, $unit);
		}
	}
}
