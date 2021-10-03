<?php
declare (strict_types = 1);
namespace Lemuria\Engine\Fantasya\Command\Create;

use Lemuria\Engine\Fantasya\Command;
use Lemuria\Engine\Fantasya\Command\Create\RawMaterial\BasicRawMaterial;
use Lemuria\Engine\Fantasya\Command\Create\RawMaterial\MineIron;
use Lemuria\Engine\Fantasya\Command\Create\RawMaterial\QuarryStone;
use Lemuria\Engine\Fantasya\Command\Create\RawMaterial\SawmillWood;
use Lemuria\Engine\Fantasya\Command\DelegatedCommand;
use Lemuria\Engine\Fantasya\Context;
use Lemuria\Engine\Fantasya\Exception\InvalidCommandException;
use Lemuria\Engine\Fantasya\Factory\Model\Job;
use Lemuria\Engine\Fantasya\Phrase;
use Lemuria\Model\Fantasya\Building\Mine;
use Lemuria\Model\Fantasya\Building\Quarry;
use Lemuria\Model\Fantasya\Building\Sawmill;
use Lemuria\Model\Fantasya\Commodity\Iron;
use Lemuria\Model\Fantasya\Commodity\Stone;
use Lemuria\Model\Fantasya\Commodity\Wood;
use Lemuria\Model\Fantasya\RawMaterial as RawMaterialInterface;

/**
 * Implementation of command MACHEN <amount> <Resource> (create resource).
 *
 * The command creates new raw material resources and adds them to the executing unit's inventory.
 *
 * - MACHEN <RawMaterial>
 * - MACHEN <amount> <RawMaterial>
 */
final class RawMaterial extends DelegatedCommand
{
	public function __construct(Phrase $phrase, Context $context, private Job $job) {
		parent::__construct($phrase, $context);
	}

	protected function createDelegate(): Command {
		$resource = $this->job->getObject();
		if (!($resource instanceof RawMaterialInterface)) {
			throw new InvalidCommandException($this, 'unknown resource');
		}

		if ($resource instanceof Wood && $this->unit->Construction()?->Building() instanceof Sawmill) {
			return new SawmillWood($this->phrase, $this->context, $this->job);
		}
		if ($resource instanceof Stone && $this->unit->Construction()?->Building() instanceof Quarry) {
			return new QuarryStone($this->phrase, $this->context, $this->job);
		}
		if ($resource instanceof Iron && $this->unit->Construction()?->Building() instanceof Mine) {
			return new MineIron($this->phrase, $this->context, $this->job);
		}

		return new BasicRawMaterial($this->phrase, $this->context, $this->job);
	}
}
