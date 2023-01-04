<?php
declare (strict_types = 1);
namespace Lemuria\Engine\Fantasya\Command\Exception;

use Lemuria\Engine\Fantasya\Message\Exception;

class TempUnitNotMappedException extends TempUnitException
{
	public function __construct(private readonly string $temp) {
		parent::__construct('TEMP unit ' . $temp . ' is not mapped.');
		$this->translationKey = Exception::TempUnitNotMapped;
	}

	protected function translate(string $template): string {
		return str_replace('$temp', $this->temp, $template);
	}
}
