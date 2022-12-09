<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Message;

use Lemuria\Model\Domain;

interface Announcement
{
	public final const SENDER = 'sender';

	public final const RECIPIENT = 'recipient';

	public function Report(): Domain;

	public function Recipient(): string;

	public function Sender(): string;

	public function Message(): string;

	public function init(LemuriaMessage $message): void;
}
