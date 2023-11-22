<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Effect;

use function Lemuria\randChance;
use Lemuria\Engine\Fantasya\Message\Region\SignpostDecayMessage;
use Lemuria\Engine\Fantasya\Priority;
use Lemuria\Engine\Fantasya\State;
use Lemuria\Exception\UnserializeEntityException;
use Lemuria\Lemuria;
use Lemuria\Validate;

final class SignpostEffect extends AbstractConstructionEffect
{
	public const int MINIMUM_LIFE = 50;

	private const string AGE = 'age';

	private int $age = 0;

	public function __construct(State $state) {
		parent::__construct($state, Priority::After);
	}

	public function Age(): int {
		return $this->age;
	}

	public function serialize(): array {
		$data            = parent::serialize();
		$data[self::AGE] = $this->age;
		return $data;
	}

	public function unserialize(array $data): static {
		parent::unserialize($data);
		$this->age = $data[self::AGE];
		return $this;
	}

	public function resetAge(): SignpostEffect {
		$this->age = 0;
		return $this;
	}

	/**
	 * @throws UnserializeEntityException
	 */
	protected function validateSerializedData(array $data): void {
		parent::validateSerializedData($data);
		$this->validate($data, self::AGE, Validate::Int);
	}

	protected function run(): void {
		if ($this->age > self::MINIMUM_LIFE) {
			$probability = 1.0 - self::MINIMUM_LIFE / (self::MINIMUM_LIFE + ($this->age - self::MINIMUM_LIFE) ** 2);
			if (randChance($probability)) {
				$this->destroySignpost();
				Lemuria::Score()->remove($this);
			}
		}
		$this->age++;
	}

	private function destroySignpost(): void {
		$construction = $this->Construction();
		$construction->Inhabitants()->clear();
		$construction->setSize(0);
		Lemuria::Catalog()->reassign($construction);
		$region = $construction->Region();
		$region->Estate()->remove($construction);
		Lemuria::Catalog()->remove($construction);
		$this->message(SignpostDecayMessage::class, $region)->p($construction->Name());
	}
}
