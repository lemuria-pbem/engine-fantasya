<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Factory;

use Lemuria\Engine\Fantasya\Effect;
use Lemuria\Engine\Fantasya\Effect\AbstractEffect;
use Lemuria\Engine\Fantasya\State;
use Lemuria\Exception\UnserializeEntityException;
use Lemuria\SerializableTrait;
use Lemuria\Validate;

class EffectFactory
{
	use SerializableTrait;

	private readonly State $state;

	private readonly string $namespace;

	public function __construct() {
		$this->state     = State::getInstance();
		$this->namespace = substr(AbstractEffect::class, 0, strrpos(AbstractEffect::class, '\\') + 1);
	}

	public function create(array $data): Effect {
		$this->validateSerializedData($data);
		$class = $this->namespace . $data[AbstractEffect::CLASS_KEY];
		/** @var Effect $effect */
		$effect = new $class($this->state);
		$effect->unserialize($data);
		return $effect;
	}

	/**
	 * Check that a serialized data array is valid.
	 *
	 * @param array (string=>mixed) &$data
	 * @throws UnserializeEntityException
	 */
	protected function validateSerializedData(array $data): void {
		$this->validate($data, AbstractEffect::CLASS_KEY, Validate::String);
	}
}
