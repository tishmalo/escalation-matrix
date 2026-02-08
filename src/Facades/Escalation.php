<?php

namespace Tishmalo\EscalationMatrix\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @method static void handle(\Throwable $exception)
 *
 * @see \Tishmalo\EscalationMatrix\Services\EscalationService
 */
class Escalation extends Facade
{
    protected static function getFacadeAccessor()
    {
        return \Tishmalo\EscalationMatrix\Services\EscalationService::class;
    }
}
