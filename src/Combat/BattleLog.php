<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Combat;

use JetBrains\PhpStorm\ArrayShape;
use JetBrains\PhpStorm\Pure;

use Lemuria\Engine\Combat\Battle as BattleModel;
use Lemuria\Engine\Fantasya\Combat\Log\LemuriaMessage;
use Lemuria\Engine\Fantasya\Combat\Log\Message;
use Lemuria\Engine\Fantasya\Factory\BuilderTrait;
use Lemuria\Exception\UnserializeException;
use Lemuria\Id;
use Lemuria\Model\Fantasya\Party;
use Lemuria\Model\Fantasya\Region;
use Lemuria\Model\Location;
use Lemuria\Serializable;
use Lemuria\SerializableTrait;

class BattleLog implements BattleModel
{
	use BuilderTrait;
	use SerializableTrait;

	private Region $region;

	/**
	 * @var Party[]
	 */
	private array $parties = [];

	/**
	 * @var Message[]
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
			$this->region = $battle->Region();
			foreach ($battle->Attacker() as $party) {
				$this->parties[] = $party;
			}
			foreach ($battle->Defender() as $party) {
				$this->parties[] = $party;
			}
		}
	}

	public function Battle(): Battle {
		return $this->battle;
	}

	#[ArrayShape(['region' => "int", 'parties' => "array", 'messages' => "array"])]
	public function serialize(): array {
		$parties = [];
		foreach ($this->parties as $party) {
			$parties[] = $party->Id()->Id();
		}
		$messages = [];
		foreach ($this->log as $message) {
			$messages[] = $message->serialize();
		}
		return ['region' => $this->region->Id()->Id(), 'parties' => $parties, 'messages' => $messages];
	}

	public function unserialize(array $data): Serializable {
		$this->validateSerializedData($data);
		$this->region = Region::get(new Id($data['region']));
		foreach ($data['parties'] as $id) {
			$this->parties[] = Party::get(new Id($id));
		}
		$battleLogMessage = new LemuriaMessage();
		foreach ($data['messages'] as $row) {
			$message = $battleLogMessage->unserialize($row);
			$message->unserialize($row);
			$this->add($message);
		}
		return $this;
	}

	#[Pure] public function Location(): Location {
		return $this->region;
	}

	/**
	 * @return Party[]
	 */
	public function Participants(): array {
		return $this->parties;
	}

	public function add(Message $message): BattleLog {
		$this->log[] = $message;
		return $this;
	}

	/**
	 * Check that a serialized data array is valid.
	 *
	 * @param array (string=>mixed) $data
	 */
	protected function validateSerializedData(array &$data): void {
		$this->validate($data, 'region', 'int');
		$this->validate($data, 'parties', 'array');
		foreach ($data['parties'] as $id) {
			if (!is_int($id)) {
				throw new UnserializeException('Party ID must be an integer.');
			}
		}
		$this->validate($data, 'messages', 'array');
		foreach ($data['messages'] as $message) {
			if (!is_array($message)) {
				throw new UnserializeException('Message must be an array.');
			}
		}
	}
}
