<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Message\Unit\Apply;

use Lemuria\Engine\Fantasya\Message\LemuriaMessage;
use Lemuria\Engine\Message;
use Lemuria\Model\Fantasya\Commodity\Horse;
use Lemuria\Model\Fantasya\Factory\BuilderTrait;
use Lemuria\Singleton;

class HorseBlissNoneMessage extends AbstractUnitMessage
{
	use BuilderTrait;

	protected string $level = Message::FAILURE;

	protected Singleton $animal;

	protected function create(): string {
		return 'There are no ' . $this->animal . 's in the region to apply Horse Bliss.';
	}

	protected function getData(LemuriaMessage $message): void {
		parent::getData($message);
		$this->animal = self::createCommodity(Horse::class);
	}

	protected function getTranslation(string $name): string {
		return $this->commodity($name, 'animal') ?? parent::getTranslation($name);
	}
}
