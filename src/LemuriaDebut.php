<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya;

use Lemuria\Engine\Debut;
use Lemuria\Engine\Fantasya\Factory\Model\LemuriaNewcomer;
use Lemuria\Engine\Newcomer;
use Lemuria\Exception\UnknownUuidException;
use Lemuria\Lemuria;

class LemuriaDebut implements Debut
{
	/**
	 * @var array(string=>LemuriaNewcomer)
	 */
	private array $newcomers = [];

	private bool $isLoaded = false;

	public function count(): int {
		return count($this->newcomers);
	}

	/**
	 * Get a Newcomer.
	 */
	public function get(string $uuid): Newcomer {
		if (!isset($this->newcomers[$uuid])) {
			throw new UnknownUuidException($uuid);
		}
		return $this->newcomers[$uuid];
	}

	/**
	 * Get all newcomers.
	 *
	 * @return Newcomer[]
	 */
	public function getAll(): array {
		return $this->newcomers;
	}

	/**
	 * Add a newcomer to the catalog.
	 */
	public function add(Newcomer $newcomer): Debut {
		$uuid = $newcomer->Uuid();
		$this->newcomers[$uuid] = $newcomer;
		return $this;
	}

	/**
	 * Remove a newcomer from the catalog.
	 */
	public function remove(Newcomer $newcomer): Debut {
		$uuid = $newcomer->Uuid();
		unset($this->newcomers[$uuid]);
		return $this;
	}

	/**
	 * Load newcomers data.
	 */
	public function load(): Debut {
		if (!$this->isLoaded) {
			foreach (Lemuria::Game()->getNewcomers() as $data) {
				$newcomer = new LemuriaNewcomer();
				$newcomer->unserialize($data);
				$this->add($newcomer);
			}
			$this->isLoaded = true;
		}
		return $this;
	}

	/**
	 * Save newcomers data.
	 */
	public function save(): Debut {
		$data = [];
		foreach ($this->newcomers as $newcomer /* @var LemuriaNewcomer $newcomer */) {
			$data[] = $newcomer->serialize();
		}
		Lemuria::Game()->setNewcomers($data);
		return $this;
	}

	/**
	 * Clear all newcomers.
	 */
	public function clear(): Debut {
		$this->newcomers = [];
		return $this;
	}
}
