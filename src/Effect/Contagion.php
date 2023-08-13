<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Effect;

use Lemuria\Engine\Fantasya\Factory\MessageTrait;
use Lemuria\Engine\Fantasya\Factory\Model\Disease;
use Lemuria\Engine\Fantasya\Message\Region\ContagionMessage;
use Lemuria\Engine\Fantasya\Priority;
use Lemuria\Engine\Fantasya\State;
use Lemuria\Exception\UnserializeEntityException;
use Lemuria\Lemuria;
use Lemuria\Model\Fantasya\People;
use Lemuria\Serializable;
use Lemuria\Validate;

final class Contagion extends AbstractRegionEffect
{
	use MessageTrait;

	private const DISEASE = 'disease';

	private const DURATION = 'duration';

	private const UNITS = 'units';

	protected ?bool $isReassign = null;

	private int $duration;

	private Disease $disease;

	private People $units;

	public function __construct(State $state) {
		parent::__construct($state, Priority::After);
		$this->units = new People();
	}

	public function Units(): People {
		return $this->units;
	}

	public function serialize(): array {
		$data                 = parent::serialize();
		$data[self::DISEASE]  = $this->disease->name;
		$data[self::DURATION] = $this->duration;
		$data[self::UNITS]    = $this->units->serialize();
		return $data;
	}

	public function unserialize(array $data): Serializable {
		parent::unserialize($data);
		$this->disease  = Disease::parse($data[self::DISEASE]);
		$this->duration = $data[self::DURATION];
		$this->units->unserialize($data[self::UNITS]);
		return $this;
	}

	public function setDisease(Disease $disease): Contagion {
		$this->disease = $disease;
		return $this;
	}

	public function setDuration(int $rounds): Contagion {
		$this->duration = $rounds;
		return $this;
	}

	public function setName(string $name): Contagion {
		$this->name = $name;
		return $this;
	}

	/**
	 * @throws UnserializeEntityException
	 */
	protected function validateSerializedData(array $data): void {
		parent::validateSerializedData($data);
		$this->validateEnum($data, self::DISEASE, Disease::class);
		$this->validate($data, self::DURATION, Validate::Int);
		$this->validate($data, self::UNITS, Validate::Array);
	}

	protected function run(): void {
		$this->duration--;
		if ($this->duration > 0) {
			$this->message(ContagionMessage::class, $this->Region())->p($this->disease->name);
		} else {
			Lemuria::Score()->remove($this);
		}
	}
}
