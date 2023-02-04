<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Combat;

use Lemuria\Engine\Fantasya\Combat\Spell\AbstractBattleSpell;
use Lemuria\Engine\Fantasya\Command\Cast;
use Lemuria\Engine\Fantasya\Exception\ActionException;
use Lemuria\Lemuria;

class Casts
{
	/**
	 * @var array<int, array>
	 */
	protected array $casts = [];

	public function add(AbstractBattleSpell $cast): void {
		$order = $cast->Spell()->Order();
		if (!isset($this->casts)) {
			$this->casts[$order] = [];
		}
		$this->casts[$order][] = $cast;
	}

	public function cast(): Casts {
		ksort($this->casts);
		foreach ($this->casts as $order => $casts) {
			Lemuria::Log()->debug('Casting battle spells of order ' . $order . '.');
			foreach ($casts as $cast /** @var Cast $cast */) {
				Lemuria::Log()->debug('Casting ' . $cast . '.');
				try {
					$cast->cast();
				} catch (ActionException $e) {
					$cast->setException($e);
				}
			}
		}
		return $this;
	}

	public function clear(): Casts {
		$this->casts = [];
		return $this;
	}
}
