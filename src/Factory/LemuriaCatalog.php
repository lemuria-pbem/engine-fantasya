<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Factory;

use Lemuria\Engine\Fantasya\State;
use Lemuria\Engine\Fantasya\Turn\Option\ThrowOption;
use Lemuria\Exception\LemuriaException;
use Lemuria\Id;
use Lemuria\Lemuria;
use Lemuria\Model\Domain;
use Lemuria\Model\Fantasya\Factory\LemuriaCatalog as BaseCatalog;

class LemuriaCatalog extends BaseCatalog
{
	protected function checkIdSize(int $domain, int $id): void {
		if ($id > parent::ID_CHECK_THRESHOLD) {
			$domain = Domain::from($domain)->name;
			$id     = new Id($id);
			if (State::isInitialized()) {
				$throw = State::getInstance()->getTurnOptions()->ThrowExceptions();
				if ($throw[ThrowOption::CODE]) {
					throw new LemuriaException('A big ' . $domain . ' ID has just been created: ' . $id);
				}
			}
			Lemuria::Log()->critical('A big ' . $domain . ' ID has just been created: ' . $id);
		}
	}
}
