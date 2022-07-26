<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Message\Party;

use Lemuria\Engine\Message;

class ContinentUpheavalMessage extends AbstractPartyMessage
{
	protected string $level = Message::EVENT;

	protected function create(): string {
		return 'One calm evening a gentle hollow growl from afar is heard. Shorly after a georgeous and deep red ' .
			   'sunset clouds of black smoke can be spotted in the south, just before the night breaks. The supposed ' .
			   'roll of thunder lasts a whole night, while unusual strong waves git the shores, and the black clouds ' .
			   'spread over the sky and obscure the stars. The sun does not rise this memorable moring, it is ' .
			   'absolutely calm, and the superstitious peasants fear the imminent end of the world. In the evening a ' .
			   'rain of ashes starts, and pale red sunlight pierces the slowly fading plumes. After one more ' .
			   'neverending night finally dawn breaks, and nature\'s sounds return. Some time later a few courageos ' .
			   'fishermen return from sea and report they had spotted the weak outlines of a mountain range far in ' .
			   'the south beyond the sea.';
	}
}
