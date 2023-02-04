<?php
declare (strict_types = 1);
namespace Lemuria\Engine\Fantasya;

use Lemuria\Engine\Fantasya\Command\Exception\TempUnicumException;
use Lemuria\Id;

/**
 * Helper class for mapping newly created unica.
 */
class UnicumMapper
{
	/**
	 * @var array<string, Id>
	 */
	private array $map = [];

	/**
	 * Check if unicum ID is already mapped.
	 */
	public function has(string $id): bool {
		return isset($this->map[$id]);
	}

	/**
	 * Add newly created Unicum to mapper.
	 *
	 * @throws TempUnicumException
	 */
	public function map(string $tempId, Id $id): UnicumMapper {
		if ($this->has($tempId)) {
			throw new TempUnicumException('Unicum ' . $tempId . ' is mapped already.');
		}
		$this->map[$tempId] = $id;
		return $this;
	}

	/**
	 * Get newly created Unicum ID by temporary ID.
	 *
	 * @throws TempUnicumException
	 */
	public function get(string $id): Id {
		if (!$id) {
			throw new TempUnicumException('Empty ID is not allowed.');
		}
		if (!isset($this->map[$id])) {
			throw new TempUnicumException('Unicum ' . $id . ' is not mapped.');
		}
		return $this->map[$id];
	}
}
