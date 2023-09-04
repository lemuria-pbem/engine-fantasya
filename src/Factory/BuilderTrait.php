<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Factory;

use Lemuria\Engine\Fantasya\Combat\Log\Message;
use Lemuria\Engine\Fantasya\Command\Operate\Carcass as Operate;
use Lemuria\Engine\Fantasya\Effect\UnicumDisintegrate;
use Lemuria\Engine\Fantasya\Message\Casus;
use Lemuria\Engine\Fantasya\State;
use Lemuria\Exception\SingletonException;
use Lemuria\Lemuria;
use Lemuria\Engine\Fantasya\Message\MessageType;
use Lemuria\Model\Domain;
use Lemuria\Model\Fantasya\Composition\Carcass;
use Lemuria\Model\Fantasya\Race;
use Lemuria\Model\Fantasya\Resources;
use Lemuria\Model\Fantasya\Unicum;

trait BuilderTrait
{
	use GrammarTrait;

	/**
	 * Create a message type singleton.
	 *
	 * @throws SingletonException
	 */
	protected static function createMessageType(string $class): MessageType {
		$messageType = Lemuria::Builder()->create($class);
		if ($messageType instanceof MessageType) {
			return $messageType;
		}
		throw new SingletonException($class, 'message type');
	}

	/**
	 * Create a battle log message singleton.
	 */
	protected static function createBattleLogMessage(string $class): Message {
		$message = Lemuria::Builder()->create($class);
		if ($message instanceof Message) {
			return $message;
		}
		throw new SingletonException($class, 'battle log message');
	}

	/**
	 * Create a carcass unicum that disintegrates after some weeks.
	 */
	protected function createNamedCarcass(Race $race, ?Resources $inventory = null): Unicum {
		/** @var Carcass $carcass */
		$carcass = Lemuria::Builder()->create(Carcass::class);
		$carcass->setCreature($race);

		if ($inventory) {
			$property = new Resources();
			$carcass->setInventory($property);
			$property->fill($inventory);
		}

		$name   = $this->translateSingleton($carcass, casus: Casus::Nominative) . ' ' . $this->combineGrammar($race, 'ein', Casus::Genitive);
		$unicum = new Unicum();
		$unicum->setId(Lemuria::Catalog()->nextId(Domain::Unicum));
		$unicum->setComposition($carcass)->setName($name);

		$effect = new UnicumDisintegrate(State::getInstance());
		Lemuria::Score()->add($effect->setUnicum($unicum)->setRounds(Operate::DISINTEGRATE));

		return $unicum;
	}
}
