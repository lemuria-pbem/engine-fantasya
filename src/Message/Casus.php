<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Message;

enum Casus : string
{
	private const NOMINATIVE = 'nom';

	private const GENITIVE = 'gen';

	private const DATIVE = 'dat';

	private const ACCUSATIVE = 'akk';

	private const INDEX = [self::NOMINATIVE => 0, self::GENITIVE => 1, self::DATIVE => 2, self::ACCUSATIVE => 3];

	case Nominative = self::NOMINATIVE;

	case Genitive = self::GENITIVE;

	case Dative = self::DATIVE;

	case Accusative = self::ACCUSATIVE;

	public function index(): int {
		return self::INDEX[$this->value];
	}
}
