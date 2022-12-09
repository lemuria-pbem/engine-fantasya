<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Combat\Log\Message;

use Lemuria\Engine\Fantasya\Combat\Log\Entity;
use Lemuria\Model\Fantasya\BattleSpell;
use Lemuria\Model\Fantasya\Factory\BuilderTrait;
use Lemuria\Model\Fantasya\Unit;
use Lemuria\Serializable;
use Lemuria\Validate;

class BattleSpellCastMessage extends BattleSpellNoAuraMessage
{
	use BuilderTrait;

	private const GRADE = 'grade';

	protected Entity $unit;

	public function __construct(?Unit $unit = null, ?BattleSpell $spell = null,
		                                protected ?int $grade = null) {
		parent::__construct($unit, $spell);
		$this->simpleParameters[] = self::GRADE;
	}

	public function getDebug(): string {
		return 'Unit ' . $this->unit . ' casts ' . $this->spell . ' with grade ' . $this->grade . '.';
	}

	public function unserialize(array $data): Serializable {
		parent::unserialize($data);
		$this->grade = $data[self::GRADE];
		return $this;
	}

	protected function getParameters(): array {
		$parameters              = parent::getParameters();
		$parameters[self::GRADE] = $this->grade;
		return $parameters;
	}

	protected function validateSerializedData(array $data): void {
		parent::validateSerializedData($data);
		$this->validate($data, self::GRADE, Validate::Int);
	}
}
