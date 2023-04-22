<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Factory;

use function Lemuria\getClass;
use Lemuria\Engine\Fantasya\Message\Casus;
use Lemuria\Item;
use Lemuria\Model\Dictionary;
use Lemuria\Model\Exception\KeyPathException;
use Lemuria\Singleton;

trait GrammarTrait
{
	protected ?Dictionary $dictionary = null;

	/**
	 * @throws KeyPathException
	 */
	protected function translateItem(Item $item, Casus $casus = Casus::Accusative): string {
		return $this->translateGrammar($item->getObject(), $casus, $item->Count() === 1 ? 0 : 1);
	}

	/**
	 * @throws KeyPathException
	 */
	protected function translateSingleton(Singleton|string $class, int $numerus = 0, Casus $casus = Casus::Accusative): string {
		return $this->translateGrammar($class, $casus, $numerus);
	}

	/**
	 * @throws KeyPathException
	 */
	protected function translateGrammar(Singleton|string $class, Casus $casus, int $numerus = 0): string {
		$this->initDictionary();
		$singleton  = $this->dictionary->raw('singleton.' . getClass($class));
		$index      = $casus->index() + 1;
		$numeri     = $singleton[$index];
		if (is_int($numeri)) {
			$numeri = $singleton[$numeri];
		}

		$numerus = $numeri[$numerus] ?? $numeri[1];
		if (is_int($numerus)) {
			$c       = (int)($numerus / 2) + 1;
			$n       = $numerus % 2;
			$numerus = $singleton[$c][$n];
		}

		return $numerus;
	}

	protected function combineGrammar(Singleton|string $singleton, string $grammar, Casus $casus = Casus::Accusative): string {
		$this->initDictionary();

		$singletonGrammay = $this->dictionary->raw('singleton.' . $singleton);
		$genus            = $singletonGrammay[0];
		$index            = $casus->index();

		$grammar   = $this->dictionary->raw('grammar.' . $grammar);
		$numerus   = $grammar['numerus'];

		$replace     = $grammar[$genus][$index];
		$replacement = $this->translateGrammar($singleton, $casus, $numerus);
		return $replace . ' ' . $replacement;
	}

	private function initDictionary(): void {
		if (!$this->dictionary) {
			$this->dictionary = new Dictionary();
		}
	}
}
