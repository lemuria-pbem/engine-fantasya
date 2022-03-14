<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Combat\Log\Message;

use JetBrains\PhpStorm\ArrayShape;
use JetBrains\PhpStorm\Pure;

use Lemuria\Serializable;

abstract class AbstractOverrunMessage extends AbstractMessage
{
	protected array $simpleParameters = ['additional'];

	public function __construct(protected ?int $additional = null) {
	}

	public function unserialize(array $data): Serializable {
		parent::unserialize($data);
		$this->additional = $data['additional'];
		return $this;
	}

	#[ArrayShape(['additional' => 'int'])]
	#[Pure] protected function getParameters(): array {
		return ['additional' => $this->additional];
	}

	protected function translate(string $template): string {
		$message = parent::translate($template);
		$index   = $this->additional > 1 ? 1 : 0;
		$will    = parent::dictionary()->get('combat.will', $index);
		$message = str_replace('$will', $will, $message);
		$fighter = parent::dictionary()->get('combat.fighter', $index);
		return str_replace('$fighter', $fighter, $message);
	}

	protected function validateSerializedData(array &$data): void {
		parent::validateSerializedData($data);
		$this->validate($data, 'additional', 'int');
	}
}
