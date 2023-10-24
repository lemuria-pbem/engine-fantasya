<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya;

use Lemuria\Engine\Fantasya\Exception\ScriptException;
use Lemuria\Storage\Ini\SectionList;

class Script
{
	public function __construct(private readonly string $file, private SectionList $data) {
	}

	public function File(): string {
		return $this->file;
	}

	public function Data(): SectionList {
		return $this->data;
	}

	public function play(): static {
		throw new ScriptException('Script data of file ' . $this->file . ' is not supported yet.');
	}
}
