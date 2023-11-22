<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Combat\Log;

use Lemuria\Exception\UnserializeException;
use Lemuria\SerializableTrait;
use Lemuria\Validate;

class LemuriaMessage
{
	use SerializableTrait;

	private const string NAMESPACE = __NAMESPACE__ . '\\Message\\';

	private const string TYPE = 'type';

	public function unserialize(array $data): Message {
		$this->validateSerializedData($data);
		$class = self::NAMESPACE . $data[self::TYPE];
		if (class_exists($class)) {
			return new $class();
		}
		throw new UnserializeException('Unknown battle log message class: ' . $data[self::TYPE]);
	}

	/**
	 * Check that a serialized data array is valid.
	 *
	 * @param array<string, mixed> $data
	 */
	protected function validateSerializedData(array $data): void {
		$this->validate($data, self::TYPE, Validate::String);
	}
}
