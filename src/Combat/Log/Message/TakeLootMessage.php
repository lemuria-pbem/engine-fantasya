<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Combat\Log\Message;

use function Lemuria\getClass;
use Lemuria\Engine\Fantasya\Combat\Log\Entity;
use Lemuria\Model\Fantasya\Factory\BuilderTrait;
use Lemuria\Model\Fantasya\Quantity;
use Lemuria\Model\Fantasya\Unit;
use Lemuria\Serializable;

class TakeLootMessage extends AbstractMessage
{
	use BuilderTrait;

	protected array $simpleParameters = ['unit'];

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
		$this->unit = Entity::create($data['id'], $data['name']);
		$this->loot = new Quantity(self::createCommodity($data['commodity']), $data['count']);
		return $this;
	}

		protected function getParameters(): array {
		return ['unit'      => $this->unit->id->Id(),              'name'  => $this->unit->name,
			    'commodity' => getClass($this->loot->Commodity()), 'count' => $this->loot->Count()];
	}

	protected function translate(string $template): string {
		$message   = parent::translate($template);
		$commodity = getClass($this->loot->Commodity());
		$count     = $this->loot->Count();
		$item      = parent::dictionary()->get('resource.' . $commodity, $count > 1 ? 1 : 0);
		$loot      = $count . ' ' . $item;
		return str_replace('$loot', $loot, $message);
	}

	protected function validateSerializedData(array &$data): void {
		parent::validateSerializedData($data);
		$this->validate($data, 'unit', 'int');
		$this->validate($data, 'name', 'string');
		$this->validate($data, 'commodity', 'string');
		$this->validate($data, 'count', 'int');
	}
}
