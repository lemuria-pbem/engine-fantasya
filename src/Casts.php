<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya;

use Lemuria\Engine\Fantasya\Command\Cast;
use Lemuria\Engine\Fantasya\Exception\ActionException;
use Lemuria\Lemuria;

class Casts
{
	/**
	 * @var array(int=>array)
	 */
	protected array $casts = [];

	protected bool $hasRun = false;

	public function add(Cast $cast): void {
		$order = $cast->Spell()->Order();
		if (!isset($this->casts)) {
			$this->casts[$order] = [];
		}
		$this->casts[$order][] = $cast;
	}

	public function cast(): void {
		if (!$this->hasRun) {
			ksort($this->casts);
			foreach ($this->casts as $order => $casts) {
				Lemuria::Log()->debug('Casting spells of order ' . $order . '.');
				foreach ($casts as $cast /* @var Cast $cast */) {
					Lemuria::Log()->debug('Casting ' . $casts . '.');
					try {
						$cast->cast();
					} catch (ActionException $e) {
						$cast->setException($e);
					}
				}
			}
			$this->hasRun = true;
		}
	}
}
