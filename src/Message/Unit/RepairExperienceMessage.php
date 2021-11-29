<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Message\Unit;

class RepairExperienceMessage extends CommodityExperienceMessage
{
	public const TALENT = parent::TALENT;

	public const ARTIFACT = parent::ARTIFACT;

	protected function create(): string {
		return 'Unit ' . $this->id . ' has not enough experience in ' . $this->talent . ' to repair ' . $this->artifact . '.';
	}
}
