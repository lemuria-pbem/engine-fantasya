<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Message\Unit\Apply;

use Lemuria\Engine\Fantasya\Message\LemuriaMessage;
use Lemuria\Engine\Fantasya\Message\Unit\AbstractUnitMessage;
use Lemuria\Engine\Message\Result;
use Lemuria\Model\Fantasya\Commodity\Peasant;
use Lemuria\Model\Fantasya\Factory\BuilderTrait;
use Lemuria\Singleton;

class PeasantJoyNoneMessage extends AbstractUnitMessage
{
	use BuilderTrait;

	protected Result $result = Result::Failure;

	protected Singleton $peasant;

	protected function create(): string {
		return 'There are no ' . $this->peasant . 's in the region to apply Peasant Joy.';
	}

	protected function getData(LemuriaMessage $message): void {
		parent::getData($message);
		$this->peasant = self::createCommodity(Peasant::class);
	}

	protected function getTranslation(string $name): string {
		return $this->commodity($name, 'peasant') ?? parent::getTranslation($name);
	}
}
