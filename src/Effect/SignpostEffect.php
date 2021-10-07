<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Effect;

use JetBrains\PhpStorm\ArrayShape;
use JetBrains\PhpStorm\Pure;

use function Lemuria\randChance;
use Lemuria\Engine\Fantasya\Action;
use Lemuria\Engine\Fantasya\Message\Region\SignpostDecayMessage;
use Lemuria\Engine\Fantasya\State;
use Lemuria\Exception\UnserializeEntityException;
use Lemuria\Lemuria;
use Lemuria\Serializable;

final class SignpostEffect extends AbstractConstructionEffect
{
	public const MINIMUM_LIFE = 50;

	private int $age = 0;

	public function __construct(State $state) {
		parent::__construct($state, Action::AFTER);
	}

	public function Age(): int {
		return $this->age;
	}

	#[ArrayShape(['class' => 'string', 'id' => "int", 'age' => 'int'])]
	#[Pure] public function serialize(): array {
		$data        = parent::serialize();
		$data['age'] = $this->age;
		return $data;
	}

	public function unserialize(array $data): Serializable {
		parent::unserialize($data);
		$this->age = $data['age'];
		return $this;
	}

	public function resetAge(): SignpostEffect {
		$this->age = 0;
		return $this;
	}

	/**
	 * @param array (string=>mixed) &$data
	 * @throws UnserializeEntityException
	 */
	protected function validateSerializedData(array &$data): void {
		parent::validateSerializedData($data);
		$this->validate($data, 'age', 'int');
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
