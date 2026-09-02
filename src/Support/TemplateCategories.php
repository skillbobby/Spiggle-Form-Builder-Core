<?php

namespace Spiggle\FormBuilder\Support;

class TemplateCategories
{
    /**
     * @return array<string, array{label: string, icon: string, sort: int}>
     */
    public static function all(): array
    {
        return [
            'order-forms' => [
                'label' => 'Order Forms',
                'icon' => 'shopping-cart',
                'sort' => 10,
            ],
            'registration-forms' => [
                'label' => 'Registration Forms',
                'icon' => 'clipboard-document-check',
                'sort' => 20,
            ],
            'application-forms' => [
                'label' => 'Application Forms',
                'icon' => 'document-text',
                'sort' => 30,
            ],
            'booking-reservation' => [
                'label' => 'Booking & Reservation',
                'icon' => 'calendar-days',
                'sort' => 40,
            ],
            'request-forms' => [
                'label' => 'Request Forms',
                'icon' => 'inbox-arrow-down',
                'sort' => 50,
            ],
            'consent-waiver' => [
                'label' => 'Consent & Waiver',
                'icon' => 'shield-check',
                'sort' => 60,
            ],
            'feedback-questionnaires' => [
                'label' => 'Feedback & Questionnaires',
                'icon' => 'chat-bubble-left-right',
                'sort' => 70,
            ],
            'human-resources' => [
                'label' => 'Human Resources & Employee',
                'icon' => 'user-group',
                'sort' => 80,
            ],
            'payment-forms' => [
                'label' => 'Payment Forms',
                'icon' => 'credit-card',
                'sort' => 90,
            ],
            'management-operations' => [
                'label' => 'Management & Operations',
                'icon' => 'building-office',
                'sort' => 100,
            ],
            'real-estate-property' => [
                'label' => 'Real Estate & Property',
                'icon' => 'home-modern',
                'sort' => 110,
            ],
            'quotation-forms' => [
                'label' => 'Quotation Forms',
                'icon' => 'calculator',
                'sort' => 120,
            ],
        ];
    }

    public static function label(string $key): string
    {
        return self::all()[$key]['label'] ?? ucfirst(str_replace('-', ' ', $key));
    }

    public static function icon(string $key): string
    {
        return self::all()[$key]['icon'] ?? 'document-text';
    }

    /**
     * @return list<string>
     */
    public static function keys(): array
    {
        $categories = self::all();
        uasort($categories, fn (array $a, array $b): int => $a['sort'] <=> $b['sort']);

        return array_keys($categories);
    }
}
