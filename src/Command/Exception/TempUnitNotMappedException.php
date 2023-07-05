<?php
declare (strict_types = 1);
namespace Lemuria\Engine\Fantasya\Command\Exception;

use Lemuria\Engine\Fantasya\Message\Exception;

class TempUnitNotMappedException extends TempUnitException
{
	private readonly string $temp;

	public function __construct(string $temp) {
		$this->temp = $this->cleanTemp($temp);
		parent::__construct('TEMP unit ' . $this->temp . ' is not mapped.');
		$this->translationKey = Exception::TempUnitNotMapped;
	}

	protected function translate(string $template): string {
		return str_replace('$temp', $this->temp, $template);
	}

	private function cleanTemp(string $temp): string {
		return trim(strip_tags($temp));
	}
}
