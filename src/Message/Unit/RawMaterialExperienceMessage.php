<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Lemuria\Message\Unit;

use Lemuria\Engine\Lemuria\Message\LemuriaMessage;
use Lemuria\Engine\Message;
use Lemuria\Singleton;

class RawMaterialExperienceMessage extends AbstractUnitMessage
{
	public const TALENT = 'talent';

	public const MATERIAL = 'material';

	protected string $level = Message::FAILURE;

	protected Singleton $talent;

	protected Singleton $material;

	/**
	 * @return string
	 */
	protected function create(): string {
		return 'Unit ' . $this->Id() . ' has not enough experience in ' . $this->talent . ' to produce ' . $this->material . '.';
	}

	/**
	 * @param LemuriaMessage $message
	 */
	protected function getData(LemuriaMessage $message): void {
		parent::getData($message);
		$this->talent = $message->getSingleton(self::TALENT);
		$this->material = $message->getSingleton(self::MATERIAL);
	}
}
