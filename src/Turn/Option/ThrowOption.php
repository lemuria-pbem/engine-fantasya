<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Turn\Option;

use Lemuria\Engine\Fantasya\Exception\OptionException;

final class ThrowOption implements \ArrayAccess
{
	public final const int NONE = 0;

	public final const int ANY = PHP_INT_MAX;

	public final const int EVALUATE = 1;

	public final const int ADD = 2;

	public final const int SUBSTITUTE = 4;

	public final const int PHP = 8;

	public final const int CODE = 16;

	private const string DEFAULT = 'ANY';

	/**
	 * @var array<string, int>|null
	 */
	private static ?array $constants = null;

	private int $options;

	public function __construct(string $default = self::DEFAULT) {
		if (!self::$constants) {
			$reflection      = new \ReflectionClass($this);
			self::$constants = $reflection->getConstants(\ReflectionClassConstant::IS_PUBLIC | \ReflectionClassConstant::IS_FINAL);
		}
		$this->parseOptionString($default);
	}

	public function offsetExists(mixed $offset): bool {
		return true;
	}

	/**
	 * @param int $offset
	 */
	public function offsetGet(mixed $offset): bool {
		return (bool)($this->options & $offset);
	}

	/**
	 * @param int $offset
	 * @param bool $value
	 */
	public function offsetSet(mixed $offset, mixed $value): void {
		if ($value) {
			$this->options |= (int)$offset;
		} else {
			$this->offsetUnset($offset);
		}
	}

	/**
	 * @param int $offset
	 */
	public function offsetUnset(mixed $offset): void {
		$this->options &= (self::ANY - $offset);
	}

	private static function parseConstant(string $name): int {
		if (!isset(self::$constants[$name])) {
			throw new OptionException('Invalid option name: ' . $name);
		}
		return self::$constants[$name];
	}

	private function parseOptionString(string $string): void {
		if (preg_match('/^([A-Z]+)([-+|][A-Z]+)*$/', $string, $matches) !== 1) {
			throw new OptionException();
		}
		$name          = $matches[1];
		$this->options = self::parseConstant($name);
		$length        = strlen($name);
		while (true) {
			$string = substr($string, $length);
			if (preg_match('/^([-+|])([A-Z]+)(.*)$/', $string, $matches) !== 1) {
				break;
			}
			$length = strlen($matches[2]) + 1;
			$value  = self::parseConstant($matches[2]);
			match ($matches[1]) {
				'+'     => $this->options += $value,
				'-'     => $this->options -= $value,
				default => $this->options |= $value
			};
		}
	}
}
