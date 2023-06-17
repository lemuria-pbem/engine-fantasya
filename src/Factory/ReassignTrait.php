<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Factory;

use Lemuria\Engine\Fantasya\Phrase;
use Lemuria\Id;
use Lemuria\Identifiable;
use Lemuria\Model\Domain;

trait ReassignTrait
{
	public function reassign(Id $oldId, Identifiable $identifiable): void {
		if ($this->checkReassignmentDomain($identifiable->Catalog())) {
			$old    = (string)$oldId;
			$new    = (string)$identifiable->Id();
			$phrase = $this->getReassignPhrase($old, $new);
			if ($phrase) {
				$oldPhrase    = $this->phrase;
				$this->phrase = $phrase;
				$this->context->getProtocol($this->unit)->reassignDefaultActivity($oldPhrase, $this);
			}
		}
	}

	public function remove(Identifiable $identifiable): void {
	}

	protected function checkReassignmentDomain(Domain $domain): bool {
		return $domain === Domain::Unit;
	}

	protected function getReassignPhrase(string $old, string $new): ?Phrase {
		$parameters = $this->getReassignPhraseParameters($old, $new);
		if ($parameters) {
			$command = $this->phrase->getVerb() . ' ' . implode(' ', $parameters);
			return new Phrase($command);
		}
		return null;
	}

	protected function getReassignPhraseForParameter(int $p, string $old, string $new): ?Phrase {
		$parameter = strtolower($this->phrase->getParameter($p));
		if ($parameter === $old) {
			$command = $this->phrase->getVerb();
			$n       = $this->phrase->count();
			for ($i = 1; $i <= $n; $i++) {
				$parameter = $i === $p ? $new : $this->phrase->getParameter($i);
				$command  .= ' ' . $parameter;
			}
			return new Phrase($command);
		}
		return null;
	}

	protected function getReassignPhraseParameters(string $old, string $new): ?array {
		$parameters = [];
		$n          = $this->phrase->count();
		for ($i = 1; $i <= $n; $i++) {
			$id              = strtolower($this->phrase->getParameter($i));
			$parameters[$id] = $i;
		}
		$i = $parameters[$old] ?? 0;
		if ($i > 0) {
			$parameters = array_keys($parameters);
			$parameters[--$i] = $new;
			return $parameters;
		}
		return null;
	}
}
