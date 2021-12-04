<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Command\Create;

use function Lemuria\randChance;
use Lemuria\Engine\Fantasya\Command\Griffin\Attack;
use Lemuria\Engine\Fantasya\Command\Griffin\Steal;
use Lemuria\Engine\Fantasya\Command\UnitCommand;
use Lemuria\Engine\Fantasya\Context;
use Lemuria\Engine\Fantasya\Factory\Model\Job;
use Lemuria\Engine\Fantasya\Phrase;
use Lemuria\Engine\Fantasya\State;
use Lemuria\Model\Fantasya\Commodity\Griffin;
use Lemuria\Model\Fantasya\Commodity\Griffinegg as GriffineggModel;
use Lemuria\Model\Fantasya\Talent\Camouflage;

/**
 * Implementation of MACHEN Greifenei, which is essentially stealing griffineggs from the region's resources, which can
 * result in an attack from breeding griffins.
 *
 * - MACHEN Greifenei|Greifeneier
 * - MACHEN <amount> Greifenei|Greifeneier
 */
final class Griffinegg extends UnitCommand
{
	public function __construct(Phrase $phrase, Context $context, private Job $job) {
		parent::__construct($phrase, $context);
	}

	protected function run(): void {
		$amount = $this->job->Count();
		if ($amount > 0) {
			$resources = $this->unit->Region()->Resources();
			$eggs      = $resources[GriffineggModel::class]->Count();
			if ($eggs > 0) {
				$chance     = 1.0;
				$personRate = $resources[Griffin::class]->Count() / $this->unit->Size();
				if ($personRate > 0) {
					$camouflage = $this->calculus()->knowledge(Camouflage::class)->Level();
					$chance     = $camouflage > 0 ? $amount * $personRate / $camouflage : 0.0;
				}
				if (randChance($chance)) {
					State::getInstance()->injectIntoTurn(new Steal($this->phrase, $this->context, $this->job));
				} else {
					State::getInstance()->injectIntoTurn(new Attack($this->phrase, $this->context));
				}
			} else {
				//TODO no eggs
			}
		}
	}
}
