<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Factory\Model;

use Lemuria\Engine\Fantasya\Factory\MessageTrait;
use Lemuria\Engine\Fantasya\Message\Vessel\EnterAlliedPortMessage;
use Lemuria\Engine\Fantasya\Message\Vessel\EnterFriendlyPortMessage;
use Lemuria\Engine\Fantasya\Message\Vessel\EnterPortDeniedMessage;
use Lemuria\Engine\Fantasya\Message\Vessel\EnterPortFullMessage;
use Lemuria\Engine\Fantasya\Message\Vessel\EnterUnguardedPortMessage;
use Lemuria\Engine\Fantasya\Message\Vessel\EnterUnmaintainedPortMessage;
use Lemuria\Engine\Fantasya\Travel\PortsTrait;
use Lemuria\Model\Fantasya\Construction;
use Lemuria\Model\Fantasya\Region;
use Lemuria\Model\Fantasya\Vessel;

class Ports
{
	use MessageTrait;
	use PortsTrait;

	public final const DUTY = 0.1;

	public function __construct(protected readonly Vessel $vessel, Region $region) {
		$this->init($vessel->Passengers()->Owner(), $region);
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

	public function CanLand(): bool {
		return $this->canBeSailedTo($this->vessel->Ship());
	}
}
