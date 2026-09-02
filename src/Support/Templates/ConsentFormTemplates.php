<?php

namespace Spiggle\FormBuilder\Support\Templates;

use Spiggle\FormBuilder\Support\TemplateBuilder as T;

class ConsentFormTemplates
{
    /**
     * @return list<array<string, mixed>>
     */
    public static function all(): array
    {
        return [
            self::informedConsent(),
            self::activitySportsWaiver(),
            self::liabilityWaiver(),
            self::photoReleaseConsent(),
        ];
    }

  /**
   * @return array<string, mixed>
   */
    protected static function informedConsent(): array
    {
        return T::make(
            'informed-consent',
            'Informed Consent',
            'Medical or research participation consent.',
            'consent-waiver',
            [
                T::page('Consent', [
                    T::content('heading', ['text' => 'Informed consent']),
                    T::content('paragraph', ['text' => 'Please read the following information carefully before signing.']),
                    T::field('participant_name', 'text', 'Participant name', ['required' => true, 'column_span' => 6]),
                    T::field('date_of_birth', 'date', 'Date of birth', ['required' => true, 'column_span' => 6]),
                    T::field('guardian_name', 'text', 'Parent / guardian (if minor)', ['column_span' => 12]),
                    T::content('paragraph', ['text' => 'I understand the nature of the procedure or study, including risks, benefits, and alternatives. I have had the opportunity to ask questions.']),
                    T::field('consent_ack', 'toggle', 'I give informed consent', ['required' => true, 'column_span' => 12]),
                    T::field('signature_date', 'date', 'Date signed', ['required' => true, 'column_span' => 6]),
                ]),
            ],
        );
    }

  /**
   * @return array<string, mixed>
   */
    protected static function activitySportsWaiver(): array
    {
        return T::make(
            'activity-sports-waiver',
            'Activity & Sports Waiver',
            'Release of liability for sports or recreational activities.',
            'consent-waiver',
            [
                T::page('Waiver', [
                    T::content('heading', ['text' => 'Activity waiver']),
                    T::field('participant_name', 'text', 'Participant name', ['required' => true, 'column_span' => 6]),
                    T::field('email', 'email', 'Email', ['required' => true, 'column_span' => 6]),
                    T::field('activity', 'select', 'Activity', ['required' => true, 'column_span' => 12, 'options' => [
                        ['label' => 'Rock climbing', 'value' => 'climbing'],
                        ['label' => 'Team sports', 'value' => 'team'],
                        ['label' => 'Swimming', 'value' => 'swim'],
                        ['label' => 'Adventure course', 'value' => 'adventure'],
                    ]]),
                    T::field('emergency_contact', 'text', 'Emergency contact', ['required' => true, 'column_span' => 6]),
                    T::field('emergency_phone', 'phone', 'Emergency phone', ['required' => true, 'column_span' => 6]),
                    T::field('medical_conditions', 'textarea', 'Medical conditions we should know', ['column_span' => 12]),
                    T::content('paragraph', ['text' => 'I acknowledge that participation involves inherent risks and I voluntarily assume those risks.']),
                    T::field('waiver_ack', 'toggle', 'I agree to the waiver terms', ['required' => true, 'column_span' => 12]),
                ]),
            ],
        );
    }

  /**
   * @return array<string, mixed>
   */
    protected static function liabilityWaiver(): array
    {
        return T::make(
            'liability-waiver',
            'Liability Waiver',
            'General liability release for events or services.',
            'consent-waiver',
            [
                T::page('Release', [
                    T::field('signer_name', 'text', 'Full legal name', ['required' => true, 'column_span' => 6]),
                    T::field('email', 'email', 'Email', ['required' => true, 'column_span' => 6]),
                    T::field('event_or_service', 'text', 'Event or service', ['required' => true, 'column_span' => 12]),
                    T::content('paragraph', ['text' => 'In consideration of being allowed to participate, I hereby release and hold harmless the organizer from any liability for injury or damage arising from participation, except where prohibited by law.']),
                    T::field('ack_risks', 'toggle', 'I understand and accept the risks', ['required' => true, 'column_span' => 12]),
                    T::field('ack_release', 'toggle', 'I agree to the release of liability', ['required' => true, 'column_span' => 12]),
                    T::field('signed_date', 'date', 'Date', ['required' => true, 'column_span' => 6]),
                ]),
            ],
        );
    }

  /**
   * @return array<string, mixed>
   */
    protected static function photoReleaseConsent(): array
    {
        return T::make(
            'photo-release-consent',
            'Photo / Media Release',
            'Consent to use photos or video for marketing.',
            'consent-waiver',
            [
                T::page('Release', [
                    T::field('subject_name', 'text', 'Name', ['required' => true, 'column_span' => 6]),
                    T::field('guardian_name', 'text', 'Parent / guardian (if under 18)', ['column_span' => 6]),
                    T::field('email', 'email', 'Email', ['column_span' => 6]),
                    T::content('paragraph', ['text' => 'I grant permission to photograph, record, and use my likeness in print and digital media for promotional purposes without compensation.']),
                    T::field('consent_photo', 'radio', 'Photo consent', ['required' => true, 'column_span' => 12, 'options' => [
                        ['label' => 'I consent', 'value' => 'yes'],
                        ['label' => 'I do not consent', 'value' => 'no'],
                    ]]),
                    T::field('signed_date', 'date', 'Date', ['required' => true, 'column_span' => 6]),
                ]),
            ],
        );
    }
}
