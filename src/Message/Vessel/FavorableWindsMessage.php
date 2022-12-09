<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Message\Vessel;

use Lemuria\Engine\Message\Section;

class FavorableWindsMessage extends AbstractVesselMessage
{
	protected Section $section = Section::Magic;

	protected function create(): string {
		return 'Favorable winds help us to sail faster.';
	}
}
