<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Storage\Migration\Upgrade;

use function Lemuria\getClass;
use Lemuria\Engine\Fantasya\Combat\Log\Entity;
use Lemuria\Engine\Fantasya\Combat\Log\Message\BattleBeginsMessage;
use Lemuria\Id;
use Lemuria\Model\Fantasya\Exception\MigrationException;
use Lemuria\Model\Fantasya\Storage\Migration\AbstractUpgrade;
use Lemuria\Model\Game;

class BattleBegins extends AbstractUpgrade
{
	private const P_ENTITY = '|^([^\]]+)\[([0-9a-z]+)\]$|';

	private const P_ENTITIES = '([^\]]+\])';

	private const PATTERN = '|^In region ' . self::P_ENTITIES . ' a battle is raging: '
	                      . 'Parties ' . self::P_ENTITIES . ' attack parties ' . self::P_ENTITIES . '\.$|';

	private const P_EXTRACT = '|^In region ([^\]]+\]) a battle is raging: Parties ([^.]+)\.$|';

	private const SPLIT = ' attack parties ';

	protected string $before = '1.2.0';

	protected string $after = '1.2.2';

	private string $battleBegins;

	public function __construct(Game $game) {
		parent::__construct($game);
		$this->battleBegins = getClass(BattleBeginsMessage::class);
	}

	public function upgrade(): static {
		$hostilities = [];
		foreach ($this->game->getHostilities() as $hostility) {
			$messages = [];
			foreach ($hostility['messages'] as $message) {
				$type = $message['type'] ?? null;
				if ($type !== $this->battleBegins) {
					$messages[] = $message;
					continue;
				}
				if (isset($message['region']) && isset($message['attackers']) && isset($message['defenders'])) {
					continue;
				}

				$debug = $message['debug'] ?? null;
				if (!$debug) {
					throw new MigrationException($this->battleBegins . ' needs a debug string to migrate.');
				}
				if (preg_match(self::PATTERN, $debug, $matches) === 1) {
					$region    = $this->parseEntity(trim($matches[1]));
					$attackers = $this->parseEntities(trim($matches[2]));
					$defenders = $this->parseEntities(trim($matches[3]));
				} else {
					$line      = $debug;
					$region    = $this->extractRegion($line);
					$attackers = $this->extractAttackers($line);
					$defenders = $this->parseEntities($line);
				}

				unset($message['debug']);
				$message['region']    = $region;
				$message['attackers'] = $attackers;
				$message['defenders'] = $defenders;
				$message['debug']     = $debug;
				$messages[]           = $message;
			}

			$hostility['messages'] = $messages;
			$hostilities[]         = $hostility;
		}

		$this->game->setHostilities($hostilities);
		return $this->finish();
	}

	private function parseEntity(string $debug): array {
		if (preg_match(self::P_ENTITY, $debug, $matches) === 1) {
			$id = Id::fromId($matches[2]);
			$name = trim($matches[1]);
			return Entity::create($id->Id(), $name)->serialize();
		}
		$this->throwMigrationException();
	}

	private function parseEntities(string $debug): array {
		$debugs = [];
		while (true) {
			$hasMore = strpos($debug, '], ');
			if ($hasMore === false) {
				$debugs[] = trim($debug);
				break;
			}
			$debugs[] = substr($debug, 0, $hasMore + 1);
			$debug    = trim(substr($debug, $hasMore + 3));
		}

		$entities = [];
		foreach ($debugs as $debug) {
			$entities[] = $this->parseEntity($debug);
		}
		return $entities;
	}

	private function extractRegion(string &$debug): array {
		if (preg_match(self::P_EXTRACT, $debug, $matches) === 1) {
			$debug = $matches[2];
			return $this->parseEntity($matches[1]);
		}
		$this->throwMigrationException();
	}

	private function extractAttackers(string &$debug): array {
		$sides = explode(self::SPLIT, $debug);
		if (count($sides) === 2) {
			$debug = $sides[1];
			return $this->parseEntities($sides[0]);
		}
		$this->throwMigrationException();
	}

	private function throwMigrationException(): void {
		throw new MigrationException($this->battleBegins . ' cannot migrate with an invalid debug string.');
	}
}
