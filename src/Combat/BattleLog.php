<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Combat;

use Lemuria\CountableTrait;
use Lemuria\Engine\Combat\Battle as BattleModel;
use Lemuria\Engine\Fantasya\Combat\Log\LemuriaMessage;
use Lemuria\Engine\Fantasya\Combat\Log\Message;
use Lemuria\Engine\Fantasya\Factory\BuilderTrait;
use Lemuria\Engine\Fantasya\Factory\Model\DisguisedParty;
use Lemuria\Exception\UnserializeException;
use Lemuria\Id;
use Lemuria\IteratorTrait;
use Lemuria\Model\Fantasya\Party;
use Lemuria\Model\Fantasya\Region;
use Lemuria\Model\Location;
use Lemuria\Serializable;
use Lemuria\SerializableTrait;
use Lemuria\Validate;

class BattleLog implements BattleModel
{
	use BuilderTrait;
	use CountableTrait;
	use IteratorTrait;
	use SerializableTrait;

	private const REGION = 'region';

	private const COUNTER = 'counter';

	private const PARTIES = 'parties';

	private const REAL = 'real';

	private const DISGUISE = 'disguise';

	private const MESSAGES = 'messages';

	private Region $region;

	private int $counter;

	/**
	 * @var array<Party>
	 */
	private array $parties = [];

	/**
	 * @var array<Message>
	 */
	protected array $log = [];

	private static BattleLog $instance;

	public static function getInstance(): BattleLog {
		return self::$instance;
	}

	public static function init(BattleLog $log): BattleLog {
		self::$instance = $log;
		return $log;
	}

	public function __construct(private ?Battle $battle = null) {
		if ($battle) {
			$this->region  = $battle->Place()->Region();
			$this->counter = $battle->counter;
			foreach ($battle->Attacker() as $party) {
				$this->parties[] = $party;
			}
			foreach ($battle->Defender() as $party) {
				$this->parties[] = $party;
			}
		}
	}

	public function Location(): Location {
		return $this->region;
	}

	public function Counter(): int {
		return $this->counter;
	}

	/**
	 * @return array<Party>
	 */
	public function Participants(): array {
		return $this->parties;
	}

	public function Battle(): Battle {
		return $this->battle;
	}

	public function current(): Message {
		return $this->log[$this->index];
	}

	public function serialize(): array {
		$parties = [];
		foreach ($this->parties as $party) {
			$parties[] = $this->serializeParty($party);
		}
		$messages = [];
		foreach ($this->log as $message) {
			$messages[] = $message->serialize();
		}
		return [
			self::REGION => $this->region->Id()->Id(), self::COUNTER => $this->counter,
			self::PARTIES => $parties, self::MESSAGES => $messages
		];
	}

	public function unserialize(array $data): Serializable {
		$this->validateSerializedData($data);
		$this->region  = Region::get(new Id($data[self::REGION]));
		$this->counter = $data[self::COUNTER];
		foreach ($data[self::PARTIES] as $id) {
			$this->parties[] = $this->initParty($id);
		}
		$battleLogMessage = new LemuriaMessage();
		foreach ($data[self::MESSAGES] as $row) {
			$message = $battleLogMessage->unserialize($row);
			$message->unserialize($row);
			$this->add($message);
		}
		return $this;
	}

	public function add(Message $message): BattleLog {
		$this->log[] = $message;
		$this->count++;
		return $this;
	}

	/**
	 * Check that a serialized data array is valid.
	 *
	 * @param array
	 */
	protected function validateSerializedData(array $data): void {
		$this->validate($data, self::REGION, Validate::Int);
		$this->validate($data, self::COUNTER, Validate::Int);
		$this->validate($data, self::PARTIES, Validate::Array);
		foreach ($data[self::PARTIES] as $id) {
			if (is_array($id)) {
				$this->validate($id, self::REAL, Validate::Int);
				$this->validate($id, self::DISGUISE, Validate::IntOrNull);
			} elseif (!is_int($id)) {
				throw new UnserializeException('Party ID must be an integer.');
			}
		}
		$this->validate($data, self::MESSAGES, Validate::Array);
		foreach ($data[self::MESSAGES] as $message) {
			if (!is_array($message)) {
				throw new UnserializeException('Message must be an array.');
			}
		}
	}

	private function initParty(array|int $id): Party {
		if (is_int($id)) {
			return Party::get(new Id($id));
		}
		$party    = new DisguisedParty(Party::get(new Id($id[self::REAL])));
		$disguise = $id[self::DISGUISE];
		if ($disguise) {
			$party->setDisguise(Party::get(new Id($disguise)));
		}
		return $party;
	}

	private function serializeParty(Party $party): int|array {
		if ($party instanceof DisguisedParty) {
			return [self::REAL => $party->Real()->Id()->Id(), self::DISGUISE => $party->Disguise()?->Id()->Id()];
		}
		return $party->Id()->Id();
	}
}
