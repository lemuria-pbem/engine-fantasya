<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Combat\Log\Message;

use JetBrains\PhpStorm\ArrayShape;
use JetBrains\PhpStorm\Pure;

use Lemuria\Engine\Fantasya\Combat\Log\Entity;
use Lemuria\Model\Fantasya\BattleSpell;
use Lemuria\Model\Fantasya\Factory\BuilderTrait;
use Lemuria\Model\Fantasya\Unit;
use Lemuria\Serializable;

class BattleSpellCastMessage extends BattleSpellNoAuraMessage
{
	use BuilderTrait;

	protected Entity $unit;

	#[Pure] public function __construct(?Unit $unit = null, ?BattleSpell $spell = null,
		                                protected ?int $grade = null) {
		parent::__construct($unit, $spell);
		$this->simpleParameters[] = 'grade';
	}

	public function getDebug(): string {
		return 'Unit ' . $this->unit . ' casts ' . $this->spell . ' with grade ' . $this->grade . '.';
	}

	public function unserialize(array $data): Serializable {
		parent::unserialize($data);
		$this->grade = $data['grade'];
		return $this;
	}

	#[ArrayShape(['id' => 'int', 'name' => 'string', 'spell' => 'string', 'grade' => 'int'])]
	#[Pure] protected function getParameters(): array {
		$parameters          = parent::getParameters();
		$parameters['grade'] = $this->grade;
		return $parameters;
	}

	protected function validateSerializedData(array &$data): void {
		parent::validateSerializedData($data);
		$this->validate($data, 'grade', 'int');
	}
}
