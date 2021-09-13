<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Combat\Log\Message;

use JetBrains\PhpStorm\ArrayShape;
use JetBrains\PhpStorm\Pure;

use function Lemuria\getClass;
use Lemuria\Engine\Fantasya\Combat\Log\Entity;
use Lemuria\Model\Fantasya\Factory\BuilderTrait;
use Lemuria\Model\Fantasya\Quantity;
use Lemuria\Model\Fantasya\Unit;
use Lemuria\Serializable;

class TakeLootMessage extends AbstractMessage
{
	use BuilderTrait;

	protected Entity $unit;

	#[Pure] public function __construct(?Unit $unit = null, protected ?Quantity $loot = null) {
		if ($unit) {
			$this->unit = new Entity($unit);
		}
	}

	public function __toString(): string {
		return $this->unit . ' takes loot: ' . $this->loot . '.';
	}

	public function unserialize(array $data): Serializable {
		parent::unserialize($data);
		$this->unit = Entity::create($data['id'], $data['name']);
		$this->loot = new Quantity(self::createCommodity($data['commodity']), $data['count']);
		return $this;
	}

	#[ArrayShape(['id' => 'int', 'name' => 'string', 'commodity' => 'string', 'count' => 'int'])]
	#[Pure]	protected function getParameters(): array {
		return ['id'        => $this->unit->id,                    'name'  => $this->unit->name,
			    'commodity' => getClass($this->loot->Commodity()), 'count' => $this->loot->Count()];
	}

	protected function validateSerializedData(array &$data): void {
		$this->validate($data, 'id', 'int');
		$this->validate($data, 'name', 'string');
		$this->validate($data, 'commodity', 'string');
		$this->validate($data, 'count', 'int');
	}
}
