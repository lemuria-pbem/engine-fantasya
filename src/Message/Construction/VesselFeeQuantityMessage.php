<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Message\Construction;

class VesselFeeQuantityMessage extends FeeQuantityMessage
{
	protected function create(): string {
		return 'The fee for the ' . $this->building . ' ' . $this->id . ' has been set to ' . $this->fee . ' per point of size.';
	}
}
