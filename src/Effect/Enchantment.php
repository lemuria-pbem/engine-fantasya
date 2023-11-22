<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Effect;

use Lemuria\Engine\Fantasya\Command\UnitCommand;
use Lemuria\Engine\Fantasya\Context;
use Lemuria\Engine\Fantasya\Factory\GrammarTrait;
use Lemuria\Engine\Fantasya\Factory\MessageTrait;
use Lemuria\Engine\Fantasya\Message\Casus;
use Lemuria\Engine\Fantasya\Message\Unit\Cast\RingOfInvisibilityEnchantmentMessage;
use Lemuria\Engine\Fantasya\Phrase;
use Lemuria\Engine\Fantasya\Priority;
use Lemuria\Engine\Fantasya\State;
use Lemuria\Exception\UnserializeEntityException;
use Lemuria\Lemuria;
use Lemuria\Model\Fantasya\Composition\RingOfInvisibility as RingOfInvisibilityComposition;
use Lemuria\Model\Fantasya\Spell\RingOfInvisibility;
use Lemuria\Model\Fantasya\Wizardry;
use Lemuria\Validate;

final class Enchantment extends AbstractUnitEffect
{
	use GrammarTrait;
	use MessageTrait;

	/**
	 * @type array<string, string>
	 */
	private const array MESSAGE = [
		RingOfInvisibility::class => RingOfInvisibilityEnchantmentMessage::class
	];

	/**
	 * @type array<string, string>
	 */
	private const array CREATE = [
		RingOfInvisibility::class => RingOfInvisibilityComposition::class
	];

	private const string ENCHANTMENTS = 'enchantments';

	private Wizardry $enchantments;

	public function __construct(State $state) {
		parent::__construct($state, Priority::After);
		$this->enchantments = new Wizardry();
	}

	public function Enchantments(): Wizardry {
		return $this->enchantments;
	}

	public function serialize(): array {
		$data                     = parent::serialize();
		$data[self::ENCHANTMENTS] = $this->enchantments->serialize();
		return $data;
	}

	public function unserialize(array $data): static {
		parent::unserialize($data);
		$this->enchantments->unserialize($data[self::ENCHANTMENTS]);
		return $this;
	}

	/**
	 * @throws UnserializeEntityException
	 */
	protected function validateSerializedData(array $data): void {
		parent::validateSerializedData($data);
		$this->validate($data, self::ENCHANTMENTS, Validate::Array);
	}

	protected function run(): void {
		if ($this->enchantments->isEmpty()) {
			Lemuria::Score()->remove($this);
		} else {
			$context = new Context(State::getInstance());
			$factory = $context->Factory();
			$unit    = $this->Unit();
			Lemuria::Log()->debug('Unit ' . $unit . ' has ' . $this->enchantments->count() . ' pending enchantments.');

			foreach ($this->enchantments as $enchantment) {
				$spell   = $enchantment->Spell()::class;
				$message = self::MESSAGE[$spell] ?? null;
				if ($message) {
					$this->message($message, $unit);
				}
				$create = self::CREATE[$spell] ?? null;
				if ($create) {
					$composition = $this->translateSingleton($create, casus: Casus::Nominative);
					/** @var UnitCommand $command */
					$command = $factory->create(new Phrase('ERSCHAFFEN ' . $composition))->getDelegate();
					$context->getProtocol($unit)->addDefault($command);
				}
				Lemuria::Log()->debug('Enchantment ' . $spell . ($create ? '(' . $create . ')' : '') . ' is pending.');
			}
		}
	}
}
