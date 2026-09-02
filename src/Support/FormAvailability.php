<?php

namespace Spiggle\FormBuilder\Support;

use Carbon\CarbonInterface;
use Spiggle\FormBuilder\Models\Form;

class FormAvailability
{
    public static function isCurrentlyActive(Form $form, ?CarbonInterface $now = null): bool
    {
        if (! $form->is_active) {
            return false;
        }

        $now = $now ?? now();

        if ($form->active_from !== null && $now->lt($form->active_from)) {
            return false;
        }

        if ($form->active_until !== null && $now->gt($form->active_until)) {
            return false;
        }

        return true;
    }

    public static function isPubliclyAvailable(Form $form, ?CarbonInterface $now = null): bool
    {
        return (bool) $form->is_published && static::isCurrentlyActive($form, $now);
    }

    public static function unavailabilityReason(Form $form, ?CarbonInterface $now = null): ?string
    {
        if (! $form->is_published) {
            return null;
        }

        if (! $form->is_active) {
            return 'This form is not accepting responses.';
        }

        $now = $now ?? now();

        if ($form->active_from !== null && $now->lt($form->active_from)) {
            return 'This form is not yet open.';
        }

        if ($form->active_until !== null && $now->gt($form->active_until)) {
            return 'This form is no longer accepting responses.';
        }

        return null;
    }

    public static function scheduleHint(Form $form): ?string
    {
        if ($form->active_from === null && $form->active_until === null) {
            return null;
        }

        $parts = [];

        if ($form->active_from !== null) {
            $parts[] = 'opens '.$form->active_from->timezone(config('app.timezone'))->format('M j, Y g:i A');
        }

        if ($form->active_until !== null) {
            $parts[] = 'closes '.$form->active_until->timezone(config('app.timezone'))->format('M j, Y g:i A');
        }

        return ucfirst(implode(' · ', $parts));
    }
}
