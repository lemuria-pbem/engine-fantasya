<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Message;

use Lemuria\Engine\Message\Section;
use Lemuria\Model\Domain;
use Lemuria\Singleton;

interface MessageType extends Singleton
{
	public function Level(): string;

	public function Report(): Domain;

	public function Section(): Section;

	public function Reliability(): Reliability;

	public function render(LemuriaMessage $message): string;
}
