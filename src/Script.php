<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya;

use Lemuria\Engine\Fantasya\Exception\ScriptException;

class Script
{
	public function __construct(private readonly string $file, private string $data) {
	}

	public function File(): string {
		return $this->file;
	}

	public function Data(): string {
		return $this->data;
	}

	public function play(): static {
		throw new ScriptException('Script data of file ' . $this->file . ' is not supported yet.');
	}
}
