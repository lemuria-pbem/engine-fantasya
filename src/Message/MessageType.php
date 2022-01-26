<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Message;

use JetBrains\PhpStorm\ExpectedValues;
use JetBrains\PhpStorm\Pure;

use Lemuria\Engine\Message\Section;
use Lemuria\Engine\Report;
use Lemuria\Model\Domain;
use Lemuria\Singleton;

interface MessageType extends Singleton
{
	#[Pure] public function Level(): string;

	#[ExpectedValues(valuesFromClass: Report::class)]
	#[Pure]
	public function Report(): Domain;

	public function Section(): Section;

	public function render(LemuriaMessage $message): string;
}
