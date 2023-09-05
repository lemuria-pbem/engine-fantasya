<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Message\Unit\Event;

use function Lemuria\getClass;
use Lemuria\Engine\Fantasya\Message\LemuriaMessage;
use Lemuria\Engine\Fantasya\Message\Unit\AbstractUnitMessage;
use Lemuria\Engine\Message\Result;
use Lemuria\Id;
use Lemuria\Model\Fantasya\Quantity;
use Lemuria\Singleton;

class MiningDiscoveryMessage extends AbstractUnitMessage
{
	protected Result $result = Result::Event;

	protected Id $region;

	protected Singleton $landscape;

	protected Quantity $discovery;

	protected function create(): string {
		return 'Unit ' . $this->id . ' discovers ' . $this->discovery . ' while working in ' . $this->landscape . ' ' . $this->region;
	}

	protected function getData(LemuriaMessage $message): void {
		parent::getData($message);
		$this->region    = $message->get();
		$this->landscape = $message->getSingleton();
		$this->discovery = $message->getQuantity();
	}

	protected function getTranslation(string $name): string {
		if ($name === 'discovery') {
			$commodity = getClass($this->discovery->Commodity());
			return $this->translateKey('event.discovery.' . $commodity);
		}
		return $this->singleton($name, 'landscape') ?? parent::getTranslation($name);
	}
}
