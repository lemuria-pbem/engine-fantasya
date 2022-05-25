<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Effect;

use function Lemuria\getClass;
use Lemuria\Engine\Fantasya\Command\UnitCommand;
use Lemuria\Engine\Fantasya\Context;
use Lemuria\Engine\Fantasya\Factory\MessageTrait;
use Lemuria\Engine\Fantasya\Message\Unit\Cast\RingOfInvisibilityEnchantmentMessage;
use Lemuria\Engine\Fantasya\Phrase;
use Lemuria\Engine\Fantasya\Priority;
use Lemuria\Engine\Fantasya\State;
use Lemuria\Exception\UnserializeEntityException;
use Lemuria\Lemuria;
use Lemuria\Model\Dictionary;
use Lemuria\Model\Fantasya\Composition\RingOfInvisibility as RingOfInvisibilityComposition;
use Lemuria\Model\Fantasya\Enchantment as EnchantmentModel;
use Lemuria\Model\Fantasya\Spell\RingOfInvisibility;
use Lemuria\Model\Fantasya\Wizardry;
use Lemuria\Serializable;

final class Enchantment extends AbstractUnitEffect
{
	use MessageTrait;

	private const MESSAGE = [
		RingOfInvisibility::class => RingOfInvisibilityEnchantmentMessage::class
	];

	private const CREATE = [
		RingOfInvisibility::class => RingOfInvisibilityComposition::class
	];

	private Wizardry $enchantments;

	public function __construct(State $state) {
		parent::__construct($state, Priority::AFTER);
		$this->enchantments = new Wizardry();
	}

	public function Enchantments(): Wizardry {
		return $this->enchantments;
	}

	public function serialize(): array {
		$data = parent::serialize();
		$data['enchantments'] = $this->enchantments->serialize();
		return $data;
	}

	public function unserialize(array $data): Serializable {
		parent::unserialize($data);
		$this->enchantments->unserialize($data['enchantments']);
		return $this;
	}

	/**
	 * @param array (string=>mixed) &$data
	 * @throws UnserializeEntityException
	 */
	protected function validateSerializedData(array &$data): void {
		parent::validateSerializedData($data);
		$this->validate($data, 'enchantments', 'array');
	}

	protected function run(): void {
		if ($this->enchantments->isEmpty()) {
			Lemuria::Score()->remove($this);
		} else {
			$context    = new Context(State::getInstance());
			$factory    = $context->Factory();
			$dictionary = new Dictionary();
			$unit       = $this->Unit();
			Lemuria::Log()->debug('Unit ' . $unit . ' has ' . $this->enchantments->count() . ' pending enchantments.');

			foreach ($this->enchantments as $enchantment /* @var EnchantmentModel $enchantment */) {
				$spell   = $enchantment->Spell()::class;
				$message = self::MESSAGE[$spell] ?? null;
				if ($message) {
					$this->message($message, $unit);
				}
				$create = self::CREATE[$spell] ?? null;
				if ($create) {
					$composition = $dictionary->get('composition.' . getClass($create));
					/** @var UnitCommand $command */
					$command = $factory->create(new Phrase('ERSCHAFFEN ' . $composition))->getDelegate();
					$context->getProtocol($unit)->addDefault($command);
				}
				Lemuria::Log()->debug('Enchantment ' . $spell . ($create ? '(' . $create . ')' : '') . ' is pending.');
			}
		}
	}
}
