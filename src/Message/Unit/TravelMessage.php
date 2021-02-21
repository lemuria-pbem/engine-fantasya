<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Lemuria\Message\Unit;

use Lemuria\Engine\Lemuria\Capacity;
use Lemuria\Engine\Lemuria\Message\LemuriaMessage;
use Lemuria\Engine\Message;

class TravelMessage extends AbstractUnitMessage
{
	protected const MOVES = [
		Capacity::DRIVE => ['drives', 'fährt'],
		Capacity::FLY   => ['flies', 'fliegt'],
		Capacity::RIDE  => ['rides', 'reitet'],
		Capacity::SHIP  => ['sails', 'segelt'],
		Capacity::WALK  => ['travels', 'reist']
	];

	protected string $level = Message::SUCCESS;

	protected string $move;

	protected array $route;

	protected function create(): string {
		$via  = $this->route;
		$from = array_shift($via);
		$to   = array_pop($via);
		$v    = count($via);

		$message = 'Unit ' . $this->id . ' ' . self::MOVES[$this->move][0] . ' from region ' . $from;
		if ($v) {
			if ($v === 1) {
				$message .= ' via region '. $via[0];
			} else {
				$message .= ' via regions ';
				$c        = --$v - 1;
				for ($i = 0; $i < $v; $i++) {
					$message .= $via[$i] . ($i < $c ? ', ' : ' ');
				}
				$message .= 'and ' . $via[$v];
			}
		}
		$message .= ' to region '. $to . '.';
		return $message;
	}

	protected function getData(LemuriaMessage $message): void {
		parent::getData($message);
		$this->move  = $message->getParameter();
		$this->route = $message->getEntities();
	}

	protected function getTranslation(string $name): string {
		if ($name === 'move') {
			return self::MOVES[$this->move][1];
		}
		if ($name === 'route') {
			$via   = $this->route;
			$from  = array_shift($via);
			$to    = array_pop($via);
			$v     = count($via);
			$route = 'von Region ' . $from;
			if ($v) {
				if ($v === 1) {
					$route .= ' über die Region '. $via[0];
				} else {
					$route .= ' über die Regionen ';
					$c      = --$v - 1;
					for ($i = 0; $i < $v; $i++) {
						$message .= $via[$i] . ($i < $c ? ', ' : ' ');
					}
					$route .= 'und ' . $via[$v];
				}
			}
			$route .= ' nach Region ' . $to;
			return $route;
		}
		return parent::getTranslation($name);
	}
}
