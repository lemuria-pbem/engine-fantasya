<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Command\Use;

use Lemuria\Engine\Fantasya\Command\Operator;
use Lemuria\Engine\Fantasya\Command\UnitCommand;
use Lemuria\Engine\Fantasya\Factory\UnicumTrait;
use Lemuria\Model\Fantasya\Practice;

/**
 * Use an unicum.
 *
 * - BENUTZEN <Unicum>
 * - BENUTZEN <composition> <Unicum>
 */
final class Operate extends UnitCommand implements Operator
{
	use UnicumTrait;

	protected function run(): void {
		$this->parseOperate(Practice::APPLY)?->apply();
	}
}
