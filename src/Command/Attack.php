<?php
declare (strict_types = 1);
namespace Lemuria\Engine\Fantasya\Command;

use Lemuria\Engine\Fantasya\Combat\BattleLog;
use Lemuria\Engine\Fantasya\Combat\Log\Message\BattleBeginsMessage;
use Lemuria\Engine\Fantasya\Message\Region\AttackBattleMessage;
use Lemuria\Lemuria;
use Lemuria\Model\Fantasya\Unit;

/**
 * Attacks other units.
 *
 * - ATTACKIEREN Monster
 * - ATTACKIEREN <race>
 * - ATTACKIEREN <unit>...
 * - ATTACKIEREN Partei <party>...
 */
final class Attack extends AssaultCommand
{
	public function from(Unit $unit): Attack {
		$this->unit = $unit;
		return $this;
	}

	protected function initialize(): void {
		if (self::$resetCampaign) {
			foreach (self::$resetCampaign as $region) {
				$this->context->resetCampaign($region);
			}
			self::$resetCampaign = null;
		}
		parent::initialize();
	}

	protected function run(): void {
		if ($this->context->getTurnOptions()->IsSimulation()) {
			return;
		}

		$region   = $this->unit->Region();
		$campaign = $this->context->getCampaign($region);
		if ($campaign->mount()) {
			$i = 0;
			foreach ($campaign->Battles() as $battle) {
				Lemuria::Log()->debug('Beginning battle ' . ++$i . ' in region ' . $battle->Place()->Region() . '.');
				$attacker = [];
				foreach ($battle->Attacker() as $party) {
					$attacker[] = $party->Name();
				}
				$defender = [];
				foreach ($battle->Defender() as $party) {
					$defender[] = $party->Name();
				}
				$this->message(AttackBattleMessage::class, $region)->p($attacker, AttackBattleMessage::ATTACKER)->p($defender, AttackBattleMessage::DEFENDER);

				$log = new BattleLog($battle);
				BattleLog::init($log)->add(new BattleBeginsMessage($battle));
				$battle->commence($this->context);
				Lemuria::Hostilities()->add($log);
			}
		}
	}
}
