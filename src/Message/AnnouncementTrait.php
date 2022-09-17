<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Message;

use Lemuria\Engine\Message;
use Lemuria\Engine\Message\Section;

trait AnnouncementTrait
{
	protected string $recipient;

	protected string $sender;

	protected string $message;

	public function __construct() {
		$this->level   = Message::EVENT;
		$this->section = Section::MAIL;
	}

	public function Recipient(): string {
		return $this->recipient;
	}

	public function Sender(): string {
		return $this->sender;
	}

	public function Message(): string {
		return $this->message;
	}

	public function init(LemuriaMessage $message): void {
		$this->getData($message);
	}

	/**
	 * @noinspection PhpMultipleClassDeclarationsInspection
	 */
	protected function getData(LemuriaMessage $message): void {
		parent::getData($message);
		$this->message   = $message->getParameter();
		$this->recipient = $message->getParameter(Announcement::RECIPIENT);
		$this->sender    = $message->getParameter(Announcement::SENDER);
	}
}
