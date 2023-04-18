<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Combat\Log\Message;

use Lemuria\Serializable;
use Lemuria\Validate;

abstract class AbstractOverrunMessage extends AbstractMessage
{
	private const ADDITIONAL = 'additional';

	protected array $simpleParameters = [self::ADDITIONAL];

	public function __construct(protected ?int $additional = null) {
		parent::__construct();
	}

	public function unserialize(array $data): Serializable {
		parent::unserialize($data);
		$this->additional = $data[self::ADDITIONAL];
		return $this;
	}

	protected function getParameters(): array {
		return [self::ADDITIONAL => $this->additional];
	}

	protected function translate(string $template): string {
		$message = parent::translate($template);
		$index   = $this->additional > 1 ? 1 : 0;
		$will    = $this->dictionary->get('combat.will', $index);
		$message = str_replace('$will', $will, $message);
		$fighter = $this->dictionary->get('combat.fighter', $index);
		return str_replace('$fighter', $fighter, $message);
	}

	protected function validateSerializedData(array $data): void {
		parent::validateSerializedData($data);
		$this->validate($data, self::ADDITIONAL, Validate::Int);
	}
}
