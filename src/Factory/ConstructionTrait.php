<?php
declare (strict_types = 1);
namespace Lemuria\Engine\Fantasya\Factory;

use Lemuria\Engine\Fantasya\Effect\DecayEffect;
use Lemuria\Engine\Fantasya\Effect\SignpostEffect;
use Lemuria\Engine\Fantasya\State;
use Lemuria\Lemuria;
use Lemuria\Model\Fantasya\Building\Canal;
use Lemuria\Model\Fantasya\Building\Castle;
use Lemuria\Model\Fantasya\Building\Market;
use Lemuria\Model\Fantasya\Building\Monument;
use Lemuria\Model\Fantasya\Building\Port;
use Lemuria\Model\Fantasya\Building\Signpost;
use Lemuria\Model\Fantasya\Building\Site;
use Lemuria\Model\Fantasya\Construction;
use Lemuria\Model\Fantasya\Extension\Duty;
use Lemuria\Model\Fantasya\Extension\Fee;
use Lemuria\Model\Fantasya\Extension\Market as MarketExtension;

trait ConstructionTrait
{
	/**
	 * @type array<string, array<string>>
	 */
	private const array EXTENSIONS = [
		Canal::class  => [Fee::class],
		Market::class => [MarketExtension::class],
		Port::class   => [Fee::class, Duty::class]
	];

	private bool $hasMarket = false;

	private function initializeMarket(Construction $construction): void {
		if ($this->hasMarket) {
			return;
		}
		if ($construction->Building() instanceof Castle && $construction->Size() > Site::MAX_SIZE) {
			$region = $construction->Region();
			if ($region->Luxuries()) {
				$marketBuilder = new MarketBuilder($this->context->getIntelligence($region));
				$marketBuilder->initPrices();
				Lemuria::Log()->debug('Market opens the first time in region ' . $region . ' - prices have been initialized.');
			} else {
				Lemuria::Log()->debug('Region ' . $region . ' produces no luxuries - no prices initialized.');
			}
		}
	}

	private function addConstructionExtensions(Construction $construction): void {
		$extensions      = $construction->Extensions();
		$building        = $construction->Building();
		$extensionsToAdd = self::EXTENSIONS[$building::class] ?? [];
		foreach ($extensionsToAdd as $class) {
			if (!$extensions->offsetExists($class)) {
				$extensions->add(new $class());
			}
		}
	}

	private function addConstructionEffects(Construction $construction): void {
		$building = $construction->Building();
		if ($building instanceof Monument) {
			$effect = $this->monumentEffect($construction);
			Lemuria::Score()->add($effect->resetAge());
		} elseif ($building instanceof Signpost) {
			$effect = $this->signpostEffect($construction);
			Lemuria::Score()->add($effect->resetAge());
		}
	}

	private function monumentEffect(Construction $monument): DecayEffect {
		$effect = new DecayEffect(State::getInstance());
		/** @var DecayEffect $monumentEffect */
		$monumentEffect = Lemuria::Score()->find($effect->setConstruction($monument)->setInterval(DecayEffect::MONUMENT));
		return $monumentEffect ?? $effect;
	}

	private function signpostEffect(Construction $signpost): SignpostEffect {
		$effect = new SignpostEffect(State::getInstance());
		/** @var SignpostEffect $signpostEffect */
		$signpostEffect = Lemuria::Score()->find($effect->setConstruction($signpost));
		return $signpostEffect ?? $effect;
	}
}
