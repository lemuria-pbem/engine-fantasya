<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Factory\Model;

use Lemuria\Id;
use Lemuria\Model\Fantasya\Party;
use Lemuria\Model\Fantasya\Party\Type;
use Lemuria\Model\Fantasya\Race;
use Lemuria\Model\Fantasya\Region;

final class DisguisedParty extends Party
{
	private ?Party $disguise = null;

	public function __construct(private readonly Party $real) {
		parent::__construct(type: $real->Type());
		$this->setName('Unbekannte Partei');
		$this->setRace($this->real->Race());
		$this->setOrigin($this->real->Origin());
	}

	public function Id(): Id {
		return $this->disguise ? $this->disguise->Id() : new Id(0);
	}

	public function Name(): string {
		return $this->disguise ? $this->disguise->Name() : parent::Name();
	}

	public function Description(): string {
		return $this->disguise ? $this->disguise->Description() : parent::Description();
	}

	public function Type(): Type {
		return $this->disguise ? $this->disguise->Type() : parent::Type();
	}

	public function Banner(): string {
		return $this->disguise ? $this->disguise->Banner() : parent::Banner();
	}

	public function Uuid(): string {
		return $this->disguise ? $this->disguise->Uuid() : parent::Uuid();
	}

	public function Race(): Race {
		return $this->disguise ? $this->disguise->Race() : parent::Race();
	}

	public function Origin(): Region {
		return $this->disguise ? $this->disguise->Origin() : parent::Origin();
	}

	public function Real(): Party {
		return $this->real;
	}

	public function Disguise(): ?Party {
		return $this->disguise;
	}

	public function setDisguise(?Party $disguise = null): DisguisedParty {
		$this->disguise = $disguise;
		return $this;
	}
}
