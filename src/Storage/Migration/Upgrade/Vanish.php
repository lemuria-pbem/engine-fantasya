<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Storage\Migration\Upgrade;

use function Lemuria\getClass;
use Lemuria\Engine\Fantasya\Effect\VanishEffect;
use Lemuria\Model\Fantasya\Storage\Migration\AbstractUpgrade;
use Lemuria\Model\Game;

class Vanish extends AbstractUpgrade
{
	protected string $before = '1.4.0';

	protected string $after = '1.5.0';

	private string $vanishEffect;

	public function __construct(Game $game) {
		parent::__construct($game);
		$this->vanishEffect = getClass(VanishEffect::class);
	}

	public function upgrade(): static {
		$effects = [];
		foreach ($this->game->getEffects() as $effect) {
			if ($effect['class'] === $this->vanishEffect) {
				unset($effect['summoner']);
			}
			$effects[] = $effect;
		}
		$this->game->setEffects($effects);
		return $this->finish();
	}
}
