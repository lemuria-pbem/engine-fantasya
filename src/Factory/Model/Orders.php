<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Factory\Model;

use Lemuria\Engine\Fantasya\Command\Comment;
use Lemuria\Engine\Fantasya\Context;
use Lemuria\Engine\Fantasya\Factory\CommandFactory;
use Lemuria\Engine\Fantasya\Phrase;
use Lemuria\Engine\Fantasya\State;
use Lemuria\Lemuria;
use Lemuria\Model\Fantasya\Unit;

class Orders
{
	public array $orders = [];

	public array $comments = [];

	public function __construct(Unit $unit) {
		$context = new Context(new State());
		$factory = new CommandFactory($context->setUnit($unit));
		foreach (Lemuria::Orders()->getCurrent($unit->Id()) as $command) {
			$comment = $factory->create(new Phrase($command))->getDelegate();
			if ($comment instanceof Comment) {
				$line = trim($comment->Line());
				if ($line) {
					$this->comments[] = $line;
				}
			} else {
				$this->orders[] = trim($command);
			}
		}
	}
}
