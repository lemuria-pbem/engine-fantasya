<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Lemuria\Storage;

use Lemuria\Model\Lemuria\Storage\JsonGame;
use Lemuria\Storage\FileProvider;

abstract class LemuriaGame extends JsonGame
{
	/**
	 * @return array(string=>string)
	 */
	protected function getStringStorage(): array {
		return ['strings.json' => new FileProvider(__DIR__ . '/../../resources')];
	}
}
