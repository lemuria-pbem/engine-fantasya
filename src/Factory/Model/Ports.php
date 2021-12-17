<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Factory\Model;

use JetBrains\PhpStorm\Pure;
use Lemuria\Engine\Fantasya\Effect\Unmaintained;
use Lemuria\Engine\Fantasya\Factory\MessageTrait;
use Lemuria\Engine\Fantasya\Message\Vessel\EnterAlliedPortMessage;
use Lemuria\Engine\Fantasya\Message\Vessel\EnterFriendlyPortMessage;
use Lemuria\Engine\Fantasya\Message\Vessel\EnterPortDeniedMessage;
use Lemuria\Engine\Fantasya\Message\Vessel\EnterPortFullMessage;
use Lemuria\Engine\Fantasya\Message\Vessel\EnterUnguardedPortMessage;
use Lemuria\Engine\Fantasya\Message\Vessel\EnterUnmaintainedPortMessage;
use Lemuria\Engine\Fantasya\State;
use Lemuria\Lemuria;
use Lemuria\Model\Fantasya\Building\Port;
use Lemuria\Model\Fantasya\Construction;
use Lemuria\Model\Fantasya\Party;
use Lemuria\Model\Fantasya\Relation;
use Lemuria\Model\Fantasya\Region;
use Lemuria\Model\Fantasya\Unit;
use Lemuria\Model\Fantasya\Vessel;

class Ports
{
	use MessageTrait;

	public const DUTY = 0.1;

	/**
	 * @var Construction[]
	 */
	protected array $friendly = [];

	/**
	 * @var Construction[]
	 */
	protected array $allied = [];

	/**
	 * @var Construction[]
	 */
	protected array $unmaintained = [];

	/**
	 * @var Construction[]
	 */
	protected array $unguarded = [];

	/**
	 * @var Construction[]
	 */
	protected array $foreign = [];

	protected Unit $captain;

	protected Party $party;

	/**
	 * @var array(int=>int)
	 */
	protected array $used = [];

	public function __construct(protected Vessel $vessel, Region $region) {
		$this->captain = $vessel->Passengers()->Owner();
		$this->party   = $this->captain->Party();
		foreach ($region->Estate() as $construction /* @var Construction $construction */) {
			if ($construction->Building() instanceof Port) {
				$this->add($construction);
			}
		}
		foreach ($region->Fleet() as $vessel /* @var Vessel $vessel */) {
			$port = $vessel->Port()?->Id()->Id();
			if ($port) {
				if (!isset($this->used[$port])) {
					$this->used[$port] = $vessel->Ship()->Captain();
				} else {
					$this->used[$port] += $vessel->Ship()->Captain();
				}
			}
		}
	}

	public function Port(): ?Construction {
		$size = $this->vessel->Ship()->Captain();
		foreach ($this->friendly as $port) {
			if ($this->hasSpace($port, $size)) {
				$this->message(EnterFriendlyPortMessage::class, $this->vessel)->e($port);
				return $port;
			}
			$this->message(EnterPortFullMessage::class, $this->vessel)->e($port);
		}
		foreach ($this->allied as $port) {
			if ($this->hasSpace($port, $size)) {
				$this->message(EnterAlliedPortMessage::class, $this->vessel)->e($port);
				return $port;
			}
			$this->message(EnterPortFullMessage::class, $this->vessel)->e($port);
		}
		foreach ($this->unmaintained as $port) {
			if ($this->hasSpace($port, $size)) {
				$this->message(EnterUnmaintainedPortMessage::class, $this->vessel)->e($port);
				return $port;
			}
			$this->message(EnterPortFullMessage::class, $this->vessel)->e($port);
		}
		foreach ($this->unguarded as $port) {
			if ($this->hasSpace($port, $size)) {
				$this->message(EnterUnguardedPortMessage::class, $this->vessel)->e($port);
				return $port;
			}
			$this->message(EnterPortFullMessage::class, $this->vessel)->e($port);
		}
		return null;
	}

	public function IsDenied(): bool {
		foreach ($this->foreign as $port) {
			$this->message(EnterPortDeniedMessage::class, $this->vessel)->e($port);
		}
		return !empty($this->foreign);
	}

	private function add(Construction $port): void {
		$master = $port->Inhabitants()->Owner();
		if (!$master || $this->isUnmaintained($port)) {
			$this->unmaintained[] = $port;
		} else {
			$party = $master->Party();
			if ($party === $this->party) {
				$this->friendly[] = $port;
			} else {
				$isGuarded = false;
				foreach ($port->Inhabitants() as $unit /* @var Unit $unit */) {
					if ($unit->IsGuarding()) {
						$isGuarded = true;
						break;
					}
				}
				if ($isGuarded) {
					if ($party->Diplomacy()->has(Relation::GUARD, $this->captain)) {
						$this->allied[] = $port;
					} else {
						$this->foreign[] = $port;
					}
				} else {
					$this->unguarded[] = $port;
				}
			}
		}
	}

	private function isUnmaintained(Construction $port): bool {
		$effect = new Unmaintained(State::getInstance());
		return Lemuria::Score()->find($effect->setConstruction($port)) instanceof Unmaintained;
	}

	#[Pure] private function hasSpace(Construction $port, int $size): bool {
		$id   = $port->Id()->Id();
		$free = $port->Size();
		if (isset($this->used[$id])) {
			$free -= $this->used[$id];
		}
		return $free >= $size;
	}
}
