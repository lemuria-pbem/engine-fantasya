<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Lemuria\Message;

use JetBrains\PhpStorm\ExpectedValues;
use JetBrains\PhpStorm\Pure;

use Lemuria\Engine\Report;
use Lemuria\Singleton;

interface MessageType extends Singleton
{
	#[Pure] public function Level(): string;

	#[ExpectedValues(valuesFromClass: Report::class)]
	#[Pure]
	public function Report(): int;

	public function render(LemuriaMessage $message): string;
}
