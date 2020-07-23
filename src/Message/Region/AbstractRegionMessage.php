<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Lemuria\Message\Region;

use Lemuria\Engine\Lemuria\Exception\MessageEntityException;
use Lemuria\Engine\Lemuria\Message\AbstractMessage;
use Lemuria\Engine\Lemuria\Message\LemuriaMessage;
use Lemuria\Engine\Report;
use Lemuria\Model\Lemuria\Region;

abstract class AbstractRegionMessage extends AbstractMessage
{
	private ?Region $region = null;

	/**
	 * @return int
	 */
	public function Report(): int {
		return Report::LOCATION;
	}

	/**
	 * @return Region
	 */
	protected function Region(): Region {
		if (!$this->region) {
			throw new MessageEntityException(Region::class);
		}
		return $this->region;
	}

	/**
	 * @param LemuriaMessage $message
	 */
	protected function getEntities(LemuriaMessage $message): void {
		$this->region = Region::get($message->get(Region::class));
	}
}
