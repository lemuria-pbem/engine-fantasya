<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Command\Use;

use Lemuria\Engine\Fantasya\Command\Operator;
use Lemuria\Engine\Fantasya\Command\UnitCommand;
use Lemuria\Engine\Fantasya\Factory\OperateTrait;
use Lemuria\Model\Fantasya\Practice;

/**
 * Use an unicum.
 *
 * - BENUTZEN <Unicum>
 * - BENUTZEN <composition> <Unicum>
 */
final class Excert extends UnitCommand implements Operator
{
	use OperateTrait;

	protected function run(): void {
		$this->parseOperate(Practice::APPLY)?->apply();
	}
}
