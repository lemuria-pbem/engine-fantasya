<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Travel\Trip;

use Lemuria\Model\Fantasya\Region;

interface Seafarer
{
	public function getId(): int;

	public function sailedTo(Region $region): void;
}
