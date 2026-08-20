<?php

namespace Spiggle\FormBuilder\Services;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Request;
use Spiggle\FormBuilder\Models\Form;
use Spiggle\FormBuilder\Models\FormAuditLog;

class AuditLogger
{
    /**
     * @param  array<string, mixed>  $payload
     */
    public function log(string $action, ?Form $form = null, array $payload = []): FormAuditLog
    {
        $log = FormAuditLog::query()->create([
            'form_id' => $form?->id,
            'user_id' => Auth::id(),
            'action' => $action,
            'payload' => $payload,
            'ip_address' => Request::ip(),
        ]);

        if (function_exists('activity')) {
            try {
                $activity = activity('form-builder')->withProperties($payload);
                if ($form) {
                    $activity->performedOn($form);
                }
                $activity->log($action);
            } catch (\Throwable) {
                // Spatie activitylog is optional.
            }
        }

        return $log;
    }
}
