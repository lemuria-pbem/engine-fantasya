<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Effect;

use Lemuria\Engine\Fantasya\Priority;
use Lemuria\Engine\Fantasya\State;
use Lemuria\Exception\UnserializeEntityException;
use Lemuria\Lemuria;
use Lemuria\Model\Fantasya\Knowledge;
use Lemuria\Validate;

final class TalentEffect extends AbstractUnitEffect
{
	private const MODIFICATIONS = 'modifications';

	protected ?bool $isReassign = null;

	private Knowledge $modifications;

	public function __construct(State $state) {
		parent::__construct($state, Priority::After);
		$this->modifications = new Knowledge();
	}

	public function Modifications(): Knowledge {
		return $this->modifications;
	}

	public function serialize(): array {
		$data                      = parent::serialize();
		$data[self::MODIFICATIONS] = $this->modifications->serialize();
		return $data;
	}

	public function unserialize(array $data): static {
		parent::unserialize($data);
		$this->modifications->unserialize($data[self::MODIFICATIONS]);
		return $this;
	}

	/**
	 * @throws UnserializeEntityException
	 */
	protected function validateSerializedData(array $data): void {
		parent::validateSerializedData($data);
		$this->validate($data, self::MODIFICATIONS, Validate::Array);
	}

	protected function run(): void {
		Lemuria::Score()->remove($this);
	}
}
