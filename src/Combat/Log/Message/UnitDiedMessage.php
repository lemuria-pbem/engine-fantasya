<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Combat\Log\Message;

use Lemuria\Engine\Fantasya\Combat\Log\Entity;
use Lemuria\Model\Fantasya\Unit;
use Lemuria\Serializable;
use Lemuria\Validate;

class UnitDiedMessage extends AbstractMessage
{
	private const ID = 'id';

	private const UNIT = 'unit';

	private const NAME = 'name';

	protected array $simpleParameters = [self::UNIT];

	protected Entity $unit;

	public function __construct(?Unit $unit = null) {
		if ($unit) {
			$this->unit = new Entity($unit);
		}
	}

	public function getDebug(): string {
		return $this->unit . ' is destroyed.';
	}

	public function unserialize(array $data): Serializable {
		parent::unserialize($data);
		$this->unit = Entity::create($data[self::ID], $data[self::NAME]);
		return $this;
	}

		protected function getParameters(): array {
		return [self::UNIT => $this->unit->id->Id(), self::NAME => $this->unit->name];
	}

	protected function validateSerializedData(array $data): void {
		parent::validateSerializedData($data);
		$this->validate($data, self::UNIT, Validate::Int);
		$this->validate($data, self::NAME, Validate::String);
	}
}
