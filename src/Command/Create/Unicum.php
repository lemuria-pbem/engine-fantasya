<?php
declare (strict_types = 1);
namespace Lemuria\Engine\Fantasya\Command\Create;

use function Lemuria\getClass;
use Lemuria\Engine\Fantasya\Activity;
use Lemuria\Engine\Fantasya\Command\UnitCommand;
use Lemuria\Engine\Fantasya\Context;
use Lemuria\Engine\Fantasya\Effect\Enchantment as EnchantmentEffect;
use Lemuria\Engine\Fantasya\Effect\UnicumRead;
use Lemuria\Engine\Fantasya\Exception\InvalidCommandException;
use Lemuria\Engine\Fantasya\Exception\UnknownCommandException;
use Lemuria\Engine\Fantasya\Factory\CollectTrait;
use Lemuria\Engine\Fantasya\Factory\OperatorActivityTrait;
use Lemuria\Engine\Fantasya\Factory\WorkloadTrait;
use Lemuria\Engine\Fantasya\Message\Unit\UnicumCreateMessage;
use Lemuria\Engine\Fantasya\Message\Unit\UnicumMaterialMessage;
use Lemuria\Engine\Fantasya\Message\Unit\UnicumNoMaterialMessage;
use Lemuria\Engine\Fantasya\Message\Unit\UnicumNoneMessage;
use Lemuria\Engine\Fantasya\Phrase;
use Lemuria\Engine\Fantasya\State;
use Lemuria\Exception\IdException;
use Lemuria\Id;
use Lemuria\Lemuria;
use Lemuria\Model\Dictionary;
use Lemuria\Model\Domain;
use Lemuria\Model\Fantasya\Composition;
use Lemuria\Model\Fantasya\Enchantment;
use Lemuria\Model\Fantasya\MagicRing;
use Lemuria\Model\Fantasya\Quantity;
use Lemuria\Model\Fantasya\Unicum as UnicumModel;

/**
 * This command creates a new Unicum.
 *
 * - ERSCHAFFEN <Composition> <ID>
 * - MACHEN <Composition> <ID>
 */
final class Unicum extends UnitCommand implements Activity
{
	use CollectTrait;
	use OperatorActivityTrait;
	use WorkloadTrait;

	private readonly Composition $composition;

	private ?Id $id = null;

	private readonly Dictionary $dictionary;

	public function __construct(Phrase $phrase, Context $context) {
		parent::__construct($phrase, $context);
		$this->initWorkload();
		$this->dictionary = new Dictionary();
	}

	public function getUnicumId(): string {
		return (string)$this->id;
	}

	protected function initialize(): void {
		parent::initialize();
		$n = $this->phrase->count();
		if ($n < 1 || $n > 2) {
			throw new InvalidCommandException($this);
		}
		$parameter = $this->phrase->getParameter();
		try {
			$this->composition = $this->context->Factory()->composition($parameter);
			$this->id          = $n === 2 ? Id::fromId($this->phrase->getParameter(2)) : null;
		} catch (UnknownCommandException $e) {
			throw new InvalidCommandException($this, 'Unknown Composition: ' . $parameter, $e);
		} catch (IdException $e) {
			throw new InvalidCommandException($this, 'Invalid ID given.', $e);
		}
	}

	protected function run(): void {
		$requirement   = $this->composition->getCraft();
		$maxProduction = $this->unit->Size() * $this->getProductivity($requirement)->Level();
		$production    = $this->reduceByWorkload($maxProduction);
		if ($production <= 0) {
			$this->message(UnicumNoneMessage::class)->s($this->composition);
			return;
		}

		if (!$this->hasMagicRingEnchantment()) {
			$material = $this->composition->getMaterial();
			if (!$material->isEmpty()) {
				foreach ($material as $quantity/* @var Quantity $quantity */) {
					$reserved = $this->collectQuantity($this->unit, $quantity->Commodity(), $quantity->Count());
					if ($reserved->Count() < $quantity->Count()) {
						$this->message(UnicumNoMaterialMessage::class)->s($this->composition);
						return;
					}
				}
				$inventory = $this->unit->Inventory();
				foreach ($material as $quantity/* @var Quantity $quantity */) {
					$inventory->remove($quantity);
					$this->message(UnicumMaterialMessage::class)->s($this->composition)->i($quantity);
				}
			}
		}

		$unicum = new UnicumModel();
		$id     = $this->createId();
		$unicum->setId($id);
		$unicum->setName($this->dictionary->get('composition.' . getClass($this->composition)) . ' ' . $id);
		$unicum->setComposition($this->composition);
		$this->addToWorkload(1);
		$this->unit->Treasury()->add($unicum);
		$this->addReadEffect()->Treasury()->add($unicum);
		$this->message(UnicumCreateMessage::class)->e($unicum)->s($this->composition);
	}

	private function hasMagicRingEnchantment(): bool {
		if ($this->composition instanceof MagicRing) {
			$effect = new EnchantmentEffect(State::getInstance());
			$effect = Lemuria::Score()->find($effect->setUnit($this->unit));
			if ($effect instanceof EnchantmentEffect) {
				$spell        = $this->composition->Enchantment();
				$enchantments = $effect->Enchantments();
				if ($enchantments->offsetExists($spell)) {
					$enchantments->remove(new Enchantment($spell));
					return true;
				}
			}
		}
		return false;
	}

	private function createId(): Id {
		if ($this->id && !Lemuria::Catalog()->has($this->id, Domain::UNICUM)) {
			$this->context->UnicumMapper()->map((string)$this->id, $this->id);
			return $this->id;
		}
		$id = Lemuria::Catalog()->nextId(Domain::UNICUM);
		if ($this->id) {
			$this->context->UnicumMapper()->map((string)$this->id, $id);
		}
		return $id;
	}

	private function addReadEffect(): UnicumRead {
		$effect   = new UnicumRead(State::getInstance());
		$existing = Lemuria::Score()->find($effect->setParty($this->unit->Party()));
		if ($existing instanceof UnicumRead) {
			return $existing;
		}
		Lemuria::Score()->add($effect);
		return $effect;
	}
}
