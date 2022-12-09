<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Combat\Log\Message;

use function Lemuria\getClass;
use Lemuria\Engine\Fantasya\Combat\Log\Entity;
use Lemuria\Model\Fantasya\Factory\BuilderTrait;
use Lemuria\Model\Fantasya\Quantity;
use Lemuria\Model\Fantasya\Unit;
use Lemuria\Serializable;
use Lemuria\Validate;

class TakeLootMessage extends AbstractMessage
{
	use BuilderTrait;

	private const ID = 'id';

	private const UNIT = 'unit';

	private const NAME = 'name';

	private const COMMODITY = 'commodity';

	private const COUNT = 'count';

	protected array $simpleParameters = [self::UNIT];

	protected Entity $unit;

	public function __construct(?Unit $unit = null, protected ?Quantity $loot = null) {
		if ($unit) {
			$this->unit = new Entity($unit);
		}
	}

	public function getDebug(): string {
		return $this->unit . ' takes loot: ' . $this->loot . '.';
	}

	public function unserialize(array $data): Serializable {
		parent::unserialize($data);
		$this->unit = Entity::create($data[self::ID], $data[self::NAME]);
		$this->loot = new Quantity(self::createCommodity($data[self::COMMODITY]), $data[self::COUNT]);
		return $this;
	}

		protected function getParameters(): array {
		return [
			self::UNIT => $this->unit->id->Id(), self::NAME => $this->unit->name,
			self::COMMODITY => getClass($this->loot->Commodity()), self::COUNT => $this->loot->Count()
		];
	}

	protected function translate(string $template): string {
		$message   = parent::translate($template);
		$commodity = getClass($this->loot->Commodity());
		$count     = $this->loot->Count();
		$item      = parent::dictionary()->get('resource.' . $commodity, $count > 1 ? 1 : 0);
		$loot      = $count . ' ' . $item;
		return str_replace('$loot', $loot, $message);
	}

	protected function validateSerializedData(array $data): void {
		parent::validateSerializedData($data);
		$this->validate($data, self::UNIT, Validate::Int);
		$this->validate($data, self::NAME, Validate::String);
		$this->validate($data, self::COMMODITY, Validate::String);
		$this->validate($data, self::COUNT, Validate::Int);
	}
}
