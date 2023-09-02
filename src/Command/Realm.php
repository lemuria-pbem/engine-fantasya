<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Command;

use Lemuria\Engine\Fantasya\Exception\Command\InvalidIdException;
use Lemuria\Engine\Fantasya\Exception\InvalidCommandException;
use Lemuria\Engine\Fantasya\Factory\RealmTrait;
use Lemuria\Engine\Fantasya\Message\Region\RealmAddedMessage;
use Lemuria\Engine\Fantasya\Message\Region\RealmDissolvedMessage;
use Lemuria\Engine\Fantasya\Message\Region\RealmFoundedMessage;
use Lemuria\Engine\Fantasya\Message\Region\RealmRemovedMessage;
use Lemuria\Engine\Fantasya\Message\Unit\RealmAddMessage;
use Lemuria\Engine\Fantasya\Message\Unit\RealmAlreadyAddedMessage;
use Lemuria\Engine\Fantasya\Message\Unit\RealmAnotherMessage;
use Lemuria\Engine\Fantasya\Message\Unit\RealmCreateMessage;
use Lemuria\Engine\Fantasya\Message\Unit\RealmDissolveMessage;
use Lemuria\Engine\Fantasya\Message\Unit\RealmDoesNotExistMessage;
use Lemuria\Engine\Fantasya\Message\Unit\RealmGovernedMessage;
use Lemuria\Engine\Fantasya\Message\Unit\RealmGuardedMessage;
use Lemuria\Engine\Fantasya\Message\Unit\RealmNoneMessage;
use Lemuria\Engine\Fantasya\Message\Unit\RealmRemoveMessage;
use Lemuria\Engine\Fantasya\Message\Unit\RealmWrongMessage;
use Lemuria\Exception\IdException;
use Lemuria\Id;
use Lemuria\Lemuria;
use Lemuria\Model\Domain;
use Lemuria\Model\Fantasya\Intelligence;
use Lemuria\Model\Fantasya\Realm as Model;

/**
 * This command is used to build realms.
 *
 * - REICH <ID>
 * - REICH <ID> Nicht
 */
final class Realm extends UnitCommand
{
	use RealmTrait;

	private Id $id;

	private Intelligence $intelligence;

	protected function initialize(): void {
		parent::initialize();
		$this->intelligence = $this->context->getIntelligence($this->unit->Region());
	}

	protected function run(): void {
		$n = $this->phrase->count();
		if ($n < 1 || $n > 2) {
			throw new InvalidCommandException($this);
		}
		$id = $this->phrase->getParameter();
		try {
			$this->id = Id::fromId($id);
		} catch (IdException $e) {
			throw new InvalidIdException($id, previous: $e);
		}
		try {
			$not = match (strtolower($this->phrase->getParameter(2))) {
				'' => false,
				'nicht' => true
			};
		} catch (\UnhandledMatchError) {
			throw new InvalidCommandException($this);
		}

		$not ? $this->remove() : $this->add();
	}

	private function add(): void {
		$realm = $this->getRealm();
		if (!$realm) {
			$this->create();
			return;
		}

		$region  = $this->intelligence->Region();
		$current = $region->Realm();
		if ($current) {
			if ($current === $realm) {
				$this->message(RealmAlreadyAddedMessage::class);
			} else {
				$this->message(RealmAnotherMessage::class);
			}
		} else {
			$party      = $this->unit->Party();
			$government = $this->intelligence->getGovernment()?->Inhabitants()->Owner()->Party();
			if ($government && $government !== $party) {
				$this->message(RealmGovernedMessage::class)->e($government);
			} else {
				if (!$government) {
					foreach ($this->intelligence->getGuards() as $unit) {
						if ($unit->Party() !== $party) {
							$this->message(RealmGuardedMessage::class);
							return;
						}
					}
				}
				if ($this->isValidNeighbour($realm, $region)) {
					$realm->Territory()->add($region);
					$this->message(RealmAddMessage::class)->p((string)$realm->Identifier());
					$this->message(RealmAddedMessage::class, $region)->e($party)->p($realm->Name());
				}
			}
		}
	}

	private function create(): void {
		$region = $this->intelligence->Region();
		if ($region->Realm()) {
			$this->message(RealmAnotherMessage::class);
		} else {
			$party      = $this->unit->Party();
			$government = $this->intelligence->getGovernment()?->Inhabitants()->Owner()->Party();
			if ($government && $government !== $party) {
				$this->message(RealmGovernedMessage::class)->e($government);
			} else {
				foreach ($this->intelligence->getGuards() as $unit) {
					if ($unit->Party() !== $party) {
						$this->message(RealmGuardedMessage::class);
						return;
					}
				}
				$realm = new Model();
				$realm->setId(Lemuria::Catalog()->nextId(Domain::Realm));
				$realm->setIdentifier($this->id)->setName('Reich ' . $this->id)->Territory()->add($region);
				$party->Possessions()->add($realm);
				$this->message(RealmCreateMessage::class)->p((string)$this->id);
				$this->message(RealmFoundedMessage::class, $region)->e($party)->p((string)$this->id);
			}
		}
	}

	private function remove(): void {
		$region  = $this->intelligence->Region();
		$current = $region->Realm();
		if (!$current) {
			$this->message(RealmNoneMessage::class);
			return;
		}
		$realm = $this->getRealm();
		if (!$realm) {
			$this->message(RealmDoesNotExistMessage::class)->p((string)$this->id);
			return;
		}
		if ($current !== $realm) {
			$this->message(RealmWrongMessage::class);
			return;
		}
		$territory = $realm->Territory();
		if ($region === $territory->Central()) {
			$this->dissolve($realm);
		} else {
			$territory->remove($region);
			$this->message(RealmRemoveMessage::class)->e($region)->p($realm->Name());
			$this->message(RealmRemovedMessage::class, $region)->p($realm->Name());
		}
	}

	private function dissolve(Model $realm): void {
		$territory = $realm->Territory();
		$central   = $territory->Central();
		foreach ($territory as $region) {
			$this->message(RealmRemovedMessage::class, $region)->p($realm->Name());
		}
		$territory->clear();
		$this->unit->Party()->Possessions()->remove($realm);
		Lemuria::Catalog()->remove($realm);
		$this->message(RealmDissolveMessage::class)->p($realm->Name());
		$this->message(RealmDissolvedMessage::class, $central)->p($realm->Name());
	}

	private function getRealm(): ?Model {
		$possessions = $this->unit->Party()->Possessions();
		$id          = $possessions->identify($this->id);
		if ($id) {
			return $possessions->offsetGet($id);
		}
		return null;
	}
}
