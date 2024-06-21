<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Factory;

use function Lemuria\getNamespace;
use Lemuria\Engine\Fantasya\Event;
use Lemuria\Engine\Fantasya\Exception\ReflectionException;
use Lemuria\Engine\Fantasya\State;

trait ReflectionTrait
{
	private const string GAME_EVENT_NAMESPACE = 'Lemuria\\Engine\\Fantasya\\Event\\Game';

	/**
	 * @throws ReflectionException
	 */
	private function validateEventClass(string $class): void {
		try {
			$reflection = new \ReflectionClass($class);
			if ($reflection->implementsInterface(Event::class)) {
				$parameters = $reflection->getConstructor()?->getParameters();
				if (is_array($parameters) && count($parameters) === 1) {
					$state = $parameters[0]->getType();
					if ($state instanceof \ReflectionNamedType && $state->getName() === State::class) {
						return;
					}
				}
			}
		} catch (\ReflectionException $e) {
			throw new ReflectionException('Invalid administration event', $class, $e);
		}
		throw new ReflectionException('Invalid administration event', $class);
	}

	/**
	 * @throws ReflectionException
	 */
	private function validateGameEventClass(string $class): void {
		$this->validateEventClass($class);
		$namespace = getNamespace($class);
		if ($namespace === self::GAME_EVENT_NAMESPACE || str_starts_with(self::GAME_EVENT_NAMESPACE . '\\', $namespace)) {
			return;
		}
		throw new ReflectionException('Invalid game event', $class);
	}
}
