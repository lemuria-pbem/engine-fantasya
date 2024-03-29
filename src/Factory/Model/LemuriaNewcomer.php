<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Factory\Model;

use Ramsey\Uuid\Uuid;

use function Lemuria\getClass;
use Lemuria\Engine\Newcomer;
use Lemuria\Id;
use Lemuria\Model\Fantasya\Factory\BuilderTrait;
use Lemuria\Model\Fantasya\Landscape;
use Lemuria\Model\Fantasya\Race;
use Lemuria\Model\Fantasya\Region;
use Lemuria\Model\Fantasya\Resources;
use Lemuria\Serializable;
use Lemuria\SerializableTrait;
use Lemuria\Validate;

class LemuriaNewcomer implements Newcomer, Serializable
{
	use BuilderTrait;
	use SerializableTrait;

	private const string UUID = 'uuid';

	private const string CREATION = 'creation';

	private const string NAME = 'name';

	private const string DESCRIPTION = 'description';

	private const string RACE = 'race';

	private const string LANDSCAPE = 'landscape';

	private const string ORIGIN = 'origin';

	private const string INVENTORY = 'inventory';

	private string $uuid;

	private int $creation;

	private string $name;

	private string $description;

	private ?Race $race = null;

	private ?Landscape $landscape = null;

	private ?Id $origin = null;

	private readonly Resources $inventory;

	public function __construct() {
		$this->uuid      = Uuid::uuid4()->toString();
		$this->creation  = time();
		$this->inventory = new Resources();
	}

	public function Uuid(): string {
		return $this->uuid;
	}

	public function Creation(): int {
		return $this->creation;
	}

	public function Name(): string {
		return $this->name;
	}

	public function Description(): string {
		return $this->description;
	}

	public function Race(): ?Race {
		return $this->race;
	}

	public function Landscape(): ?Landscape {
		return $this->landscape;
	}

	public function Origin(): ?Region {
		return $this->origin ? Region::get($this->origin) : null;
	}

	public function Inventory(): Resources {
		return $this->inventory;
	}

	public function serialize(): array {
		return [self::UUID      => $this->uuid, self::CREATION => $this->creation,
			    self::NAME      => $this->name, self::DESCRIPTION => $this->description,
			    self::RACE      => $this->race ? getClass($this->race) : null,
			    self::LANDSCAPE => $this->landscape ? getClass($this->landscape) : null,
			    self::ORIGIN    => $this->origin?->Id(),
			    self::INVENTORY => $this->inventory->serialize()
		];
	}

	public function unserialize(array $data): static {
		$this->uuid        = $data[self::UUID];
		$this->creation    = $data[self::CREATION];
		$this->name        = $data[self::NAME];
		$this->description = $data[self::DESCRIPTION];
		$this->race        = $data[self::RACE] ? self::createRace($data[self::RACE]) : null;
		$this->landscape   = $data[self::LANDSCAPE] ? self::createLandscape($data[self::LANDSCAPE]) : null;
		$this->origin      = $data[self::ORIGIN] ? new Id($data[self::ORIGIN]) : null;
		$this->inventory->unserialize($data[self::INVENTORY]);
		return $this;
	}

	public function setName(string $name): static {
		$this->name = $name;
		return $this;
	}

	public function setDescription(string $description): static {
		$this->description = $description;
		return $this;
	}

	public function setRace(?Race $race): static {
		$this->race = $race;
		return $this;
	}

	public function setLandscape(?Landscape $landscape): static {
		$this->landscape = $landscape;
		return $this;
	}

	public function setOrigin(?Region $region): static {
		$this->origin = $region?->Id();
		return $this;
	}

	/**
	 * @noinspection DuplicatedCode
	 */
	protected function validateSerializedData(array $data): void {
		$this->validate($data, self::UUID, Validate::String);
		$this->validate($data, self::CREATION, Validate::Int);
		$this->validate($data, self::NAME, Validate::String);
		$this->validate($data, self::DESCRIPTION, Validate::String);
		$this->validate($data, self::RACE, Validate::StringOrNull);
		$this->validate($data, self::LANDSCAPE, Validate::StringOrNull);
		$this->validate($data, self::ORIGIN, Validate::IntOrNull);
		$this->validate($data, self::INVENTORY, Validate::Array);
	}
}
