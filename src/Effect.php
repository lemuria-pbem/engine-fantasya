<?php
declare (strict_types = 1);
namespace Lemuria\Engine\Lemuria;

use Lemuria\Identifiable;
use Lemuria\Serializable;

/**
 * Effects are a result of events or player commands and can have a persisting influence.
 */
interface Effect extends Action, Identifiable, Serializable
{
}
