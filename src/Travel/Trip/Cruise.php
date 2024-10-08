<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Travel\Trip;

use Lemuria\Engine\Fantasya\Calculus;
use Lemuria\Engine\Fantasya\Travel\Movement;
use Lemuria\Exception\LemuriaException;
use Lemuria\Id;
use Lemuria\Model\Fantasya\Region;
use Lemuria\Model\Fantasya\Vessel;

class Cruise extends AbstractTrip
{
	protected Vessel $vessel;

	/**
	 * @var array<int, Seafarer>
	 */
	private static array $seafarer = [];

	public static function engage(Seafarer $seafarer): void {
		self::$seafarer[$seafarer->getId()] = $seafarer;
	}

	public static function entered(Vessel $vessel, Region $region): void {
		foreach (self::$seafarer as $id => $seafarer) {
			$id = new Id($id);
			if ($vessel->Passengers()->has($id)) {
				$seafarer->sailedTo($region);
			}
		}
	}

	public function __construct(Calculus $calculus) {
		$vessel = $calculus->Unit()->Vessel();
		if (!$vessel) {
			throw new LemuriaException('Cannot cruise without a ship.');
		}
		$this->vessel = $vessel;
		parent::__construct($calculus);
		$this->movement = Movement::Ship;
	}

	public function Speed(): int {
		return (int)floor($this->vessel->Completion() * $this->vessel->Ship()->Speed());
	}

	protected function calculateCapacity(): void {
		$this->capacity = (int)floor($this->vessel->Completion() * $this->vessel->Ship()->Payload());
	}

	protected function calculateKnowledge(): void {
		$this->knowledge = $this->vessel->Ship()->Captain();
	}

	protected function calculateWeight(): void {
		$this->weight = $this->vessel->Passengers()->Weight();
	}
}
