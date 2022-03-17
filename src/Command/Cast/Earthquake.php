<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Command\Cast;

use Lemuria\Engine\Fantasya\Calculus;
use Lemuria\Engine\Fantasya\Message\Region\Event\EarthquakeDestroyedMessage;
use Lemuria\Engine\Fantasya\Message\Region\Event\EarthquakeMessage;
use Lemuria\Lemuria;
use Lemuria\Model\Fantasya\Construction;
use Lemuria\Model\Fantasya\Factory\BuilderTrait;
use Lemuria\Model\Fantasya\Talent\Magic;

final class Earthquake extends AbstractCast
{
	use BuilderTrait;

	public function cast(): void {
		$unit      = $this->cast->Unit();
		$region    = $unit->Region();
		$calculus  = new Calculus($unit);
		$knowledge = $calculus->knowledge(Magic::class)->Level();
		$aura      = $this->cast->Aura();
		$damage    = sqrt($knowledge * $aura) / 100.0;

		$unit->Aura()->consume($aura);
		$this->message(EarthquakeMessage::class, $region);

		foreach ($region->Estate() as $construction /* @var Construction $construction */) {
			$size   = $construction->Size();
			$points = (int)round($damage * $size);
			$size  -= $points;
			$construction->setSize($size);
			if ($size <= 0) {
				Lemuria::Catalog()->reassign($construction);
				$construction->Inhabitants()->clear();
				$region->Estate()->remove($construction);
				Lemuria::Catalog()->remove($construction);
				$this->message(EarthquakeDestroyedMessage::class, $region)->e($construction);
			}
		}
	}
}
