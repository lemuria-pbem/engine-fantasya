<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Storage\Migration\Upgrade;

use function Lemuria\getClass;
use Lemuria\Engine\Fantasya\Effect\UnicumRead as UnicumReadEffect;
use Lemuria\Model\Fantasya\Storage\Migration\AbstractUpgrade;
use Lemuria\Model\Game;

class UnicumRead extends AbstractUpgrade
{
	protected string $before = '1.4.0';

	protected string $after = '1.4.9';

	private string $unicumRead;

	public function __construct(Game $game) {
		parent::__construct($game);
		$this->unicumRead = getClass(UnicumReadEffect::class);
	}

	public function upgrade(): static {
		$effects = [];
		foreach ($this->game->getEffects() as $effect) {
			if ($effect['class'] === $this->unicumRead) {
				$effect['inventory'] = [];
			}
			$effects[] = $effect;
		}
		$this->game->setEffects($effects);
		return $this->finish();
	}
}
