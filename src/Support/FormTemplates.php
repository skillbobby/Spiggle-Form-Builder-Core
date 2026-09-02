<?php

namespace Spiggle\FormBuilder\Support;

use Illuminate\Support\Str;
use Spiggle\FormBuilder\Support\Templates\ApplicationFormTemplates;
use Spiggle\FormBuilder\Support\Templates\BookingFormTemplates;
use Spiggle\FormBuilder\Support\Templates\ConsentFormTemplates;
use Spiggle\FormBuilder\Support\Templates\FeedbackFormTemplates;
use Spiggle\FormBuilder\Support\Templates\HumanResourcesFormTemplates;
use Spiggle\FormBuilder\Support\Templates\ManagementFormTemplates;
use Spiggle\FormBuilder\Support\Templates\OrderFormTemplates;
use Spiggle\FormBuilder\Support\Templates\PaymentFormTemplates;
use Spiggle\FormBuilder\Support\Templates\QuotationFormTemplates;
use Spiggle\FormBuilder\Support\Templates\RealEstateFormTemplates;
use Spiggle\FormBuilder\Support\Templates\RegistrationFormTemplates;
use Spiggle\FormBuilder\Support\Templates\RequestFormTemplates;

class FormTemplates
{
    /**
     * @return array<int, array<string, mixed>>
     */
    public static function all(): array
    {
        return array_merge(
            OrderFormTemplates::all(),
            RegistrationFormTemplates::all(),
            ApplicationFormTemplates::all(),
            BookingFormTemplates::all(),
            RequestFormTemplates::all(),
            ConsentFormTemplates::all(),
            FeedbackFormTemplates::all(),
            HumanResourcesFormTemplates::all(),
            PaymentFormTemplates::all(),
            ManagementFormTemplates::all(),
            RealEstateFormTemplates::all(),
            QuotationFormTemplates::all(),
        );
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public static function core(): array
    {
        return array_values(array_filter(self::all(), fn (array $t): bool => ($t['tier'] ?? 'core') === 'core'));
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public static function pro(): array
    {
        return array_values(array_filter(self::all(), fn (array $t): bool => ($t['tier'] ?? 'core') === 'pro'));
    }

    /**
     * Templates grouped by category key, preserving category sort order.
     *
     * @return array<string, array{category: array{label: string, icon: string}, templates: list<array<string, mixed>>}>
     */
    public static function groupedByCategory(): array
    {
        $grouped = [];

        foreach (TemplateCategories::keys() as $key) {
            $grouped[$key] = [
                'category' => TemplateCategories::all()[$key],
                'templates' => [],
            ];
        }

        foreach (self::all() as $template) {
            $key = (string) ($template['category'] ?? 'order-forms');
            if (! isset($grouped[$key])) {
                $grouped[$key] = [
                    'category' => [
                        'label' => TemplateCategories::label($key),
                        'icon' => TemplateCategories::icon($key),
                        'sort' => 999,
                    ],
                    'templates' => [],
                ];
            }
            $grouped[$key]['templates'][] = $template;
        }

        return array_filter($grouped, fn (array $group): bool => $group['templates'] !== []);
    }

    /**
     * @return array<string, int>
     */
    public static function countsByCategory(): array
    {
        $counts = [];
        foreach (self::groupedByCategory() as $key => $group) {
            $counts[$key] = count($group['templates']);
        }

        return $counts;
    }

    public static function find(string $slug): ?array
    {
        foreach (self::all() as $template) {
            if ($template['slug'] === $slug) {
                return $template;
            }
        }

        return null;
    }

    public static function isPro(string $slug): bool
    {
        $template = self::find($slug);

        return $template !== null && ($template['tier'] ?? 'core') === 'pro';
    }

    /**
     * @return array<string, mixed>
     */
    public static function definition(string $slug): array
    {
        $template = self::find($slug);
        if ($template === null) {
            throw new \InvalidArgumentException('Unknown form template: '.$slug);
        }

        $definition = $template['definition'];
        $containerType = ContainerTypes::resolve((string) ($definition['container_type'] ?? 'single'));

        return array_merge($definition, [
            'container_type' => $containerType,
            'schema' => SchemaNormalizer::normalize($definition['schema'] ?? []),
            'settings' => SchemaNormalizer::normalizePageChrome($definition['settings'] ?? []),
        ]);
    }

    /**
     * Sample submissions keyed by template slug for the seeder.
     *
     * @return array<string, list<array<string, mixed>>>
     */
    public static function sampleSubmissions(): array
    {
        return [
            'contact-us' => [
                ['full_name' => 'Maya Chen', 'email' => 'maya@example.com', 'phone' => '+1 555 0101', 'topic' => 'sales', 'message' => 'We would like a demo of the form builder for our membership site.', 'marketing_opt_in' => true],
                ['full_name' => 'Jonah Price', 'email' => 'jonah@example.com', 'phone' => '+1 555 0144', 'topic' => 'support', 'message' => 'The public form on mobile wraps labels oddly on our staging site.', 'marketing_opt_in' => false],
            ],
            'event-registration' => [
                ['attendee_name' => 'Sam Rivera', 'attendee_email' => 'sam@example.com', 'company' => 'Northwind', 'ticket_type' => 'workshop', 'quantity' => 2, 'dietary' => ['veg'], 'notes' => 'Need aisle seats.'],
            ],
            'job-application' => [
                ['applicant_name' => 'Chris Patel', 'applicant_email' => 'chris@example.com', 'role' => 'eng', 'years' => 6, 'skills' => ['laravel', 'filament'], 'summary' => 'Shipped Filament plugins and Livewire storefronts.', 'portfolio' => 'https://github.com/example', 'available' => '2026-09-01'],
            ],
            'customer-feedback' => [
                ['nps' => '9', 'product' => 'form-builder', 'what_worked' => 'The template gallery makes starting fast.', 'what_to_improve' => 'More conditional logic examples.'],
            ],
            'volunteer-signup' => [
                ['volunteer_name' => 'Alex Kim', 'volunteer_email' => 'alex@example.com', 'days' => ['sat', 'sun'], 'volunteer_skills' => ['First aid', 'Setup']],
            ],
            'product-order' => [
                ['customer_name' => 'Riley Brooks', 'customer_email' => 'riley@example.com', 'product' => 'pro', 'quantity' => 3, 'ship_method' => 'express'],
            ],
            'appointment-booking' => [
                ['client_name' => 'Jordan Lee', 'email' => 'jordan@example.com', 'phone' => '+1 555 0199', 'service' => 'consult', 'preferred_date' => '2026-09-15', 'preferred_time' => '11:00'],
            ],
            'donation' => [
                ['donor_name' => 'Taylor Morgan', 'email' => 'taylor@example.org', 'amount' => '50', 'fund' => 'general', 'payment_method' => 'stripe', 'anonymous' => false],
            ],
            'quote-request-rfq' => [
                ['company_name' => 'Acme Corp', 'contact_name' => 'Pat Nguyen', 'email' => 'pat@acme.example', 'response_due' => '2026-09-20', 'line_items' => 'Widget A x 100, Widget B x 50'],
            ],
        ];
    }

    /**
     * Seeder-ready form rows (published + sample submissions metadata stripped).
     *
     * @return array<int, array<string, mixed>>
     */
    public static function seederDefinitions(): array
    {
        $definitions = [];

        foreach (self::all() as $template) {
            $definition = $template['definition'];
            $definition['slug'] = $template['slug'];
            $definition['is_published'] = true;
            $definition['schema'] = SchemaNormalizer::normalize($definition['schema'] ?? []);
            $definition['container_type'] = $definition['container_type'] ?? 'single';
            $definition['settings'] = SchemaNormalizer::normalizePageChrome($definition['settings'] ?? []);
            $definition['submissions'] = self::sampleSubmissions()[$template['slug']] ?? [];
            $definitions[] = $definition;
        }

        return $definitions;
    }

    public static function uniqueSlug(string $baseSlug): string
    {
        $slug = Str::slug($baseSlug);
        $candidate = $slug;
        $i = 2;

        while (\Spiggle\FormBuilder\Models\Form::withTrashed()->where('slug', $candidate)->exists()) {
            $candidate = $slug.'-'.$i;
            $i++;
        }

        return $candidate;
    }
}
