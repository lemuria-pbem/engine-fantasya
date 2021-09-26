<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Combat\Log;

use Lemuria\Engine\Fantasya\Factory\BuilderTrait;
use Lemuria\SerializableTrait;

class LemuriaMessage
{
	use BuilderTrait;
	use SerializableTrait;

	public function unserialize(array $data): Message {
		$this->validateSerializedData($data);
		return self::createBattleLogMessage($data['type']);
	}

	/**
	 * Check that a serialized data array is valid.
	 *
	 * @param array (string=>mixed) $data
	 */
	protected function validateSerializedData(array &$data): void {
		$this->validate($data, 'type', 'string');
	}
}
