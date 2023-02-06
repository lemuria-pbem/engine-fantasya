<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Travel;

use function Lemuria\getClass;
use Lemuria\Model\Fantasya\Animal;
use Lemuria\Model\Fantasya\Commodity\Camel;
use Lemuria\Model\Fantasya\Commodity\Carriage;
use Lemuria\Model\Fantasya\Commodity\Elephant;
use Lemuria\Model\Fantasya\Commodity\Griffin;
use Lemuria\Model\Fantasya\Commodity\Horse;
use Lemuria\Model\Fantasya\Commodity\Pegasus;
use Lemuria\Model\Fantasya\Commodity\Weapon\Catapult;
use Lemuria\Model\Fantasya\Commodity\Weapon\WarElephant;
use Lemuria\Model\Fantasya\Factory\BuilderTrait;
use Lemuria\Model\Fantasya\Quantity;
use Lemuria\Model\Fantasya\Transport;
use Lemuria\Model\Fantasya\Unit;

class Conveyance
{
	use BuilderTrait;

	/**
	 * @var array<string, Quantity>
	 */
	private array $animal = [];

	/**
	 * @var array<string, Quantity>
	 */
	private array $transport = [];

	private int $payload = 0;

	public function __construct(Unit $unit) {
		foreach ($unit->Inventory() as $quantity) {
			$commodity = $quantity->Commodity();
			if ($commodity instanceof Animal) {
				$class = getClass($commodity);
				$this->animal[$class] = $quantity;
			} elseif ($commodity instanceof Transport) {
				$class = getClass($commodity);
				$this->transport[$class] = $quantity;
			} else {
				$this->payload += $quantity->Weight();
			}
		}
		foreach ($unit->Treasury() as $unicum) {
			$this->payload += $unicum->Composition()->Weight();
		}
	}

	public function Horse(): int {
		return $this->animal(Horse::class);
	}

	public function Pegasus(): int {
		return $this->animal(Pegasus::class);
	}

	public function Camel(): int {
		return $this->animal(Camel::class);
	}

	public function Elephant(): int {
		return $this->animal(Elephant::class);
	}

	public function WarElephant(): int {
		return $this->animal(WarElephant::class);
	}

	public function Griffin(): int {
		return $this->animal(Griffin::class);
	}

	public function Carriage(): int {
		return $this->transport(Carriage::class);
	}

	public function Catapult(): int {
		return $this->transport(Catapult::class);
	}

	public function getPayload(): int {
		return $this->payload;
	}

	public function getQuantity(string|Animal|Transport $commodity): Quantity {
		if (is_string($commodity)) {
			$commodity = self::createCommodity($commodity);
		}
		$class = getClass($commodity);
		if ($commodity instanceof Animal) {
			$quantity = $this->animal[$class] ?? null;
		} else {
			$quantity = $this->transport[$class] ?? null;
		}
		if ($quantity) {
			return $quantity;
		}
		return new Quantity($commodity, 0);
	}

	protected function animal(string|Animal $animal): int {
		$class = getClass($animal);
		return isset($this->animal[$class]) ? $this->animal[$class]->Count() : 0;
	}

	protected function transport(string|Transport $transport): int {
		$class = getClass($transport);
		return isset($this->transport[$class]) ? $this->transport[$class]->Count() : 0;
	}
}
