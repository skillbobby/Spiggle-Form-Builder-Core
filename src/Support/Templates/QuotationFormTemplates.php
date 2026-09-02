<?php

namespace Spiggle\FormBuilder\Support\Templates;

use Spiggle\FormBuilder\Support\TemplateBuilder as T;

class QuotationFormTemplates
{
    /**
     * @return list<array<string, mixed>>
     */
    public static function all(): array
    {
        return [
            self::constructionQuote(),
            self::repairQuote(),
            self::serviceQuote(),
            self::consultingQuote(),
            self::quoteRequestRfq(),
        ];
    }

  /**
   * @return array<string, mixed>
   */
    protected static function constructionQuote(): array
    {
        return T::make(
            'construction-quote',
            'Construction Quote',
            'Request a quote for construction or renovation work.',
            'quotation-forms',
            [
                T::page('Project', [
                    T::field('contact_name', 'text', 'Contact name', ['required' => true, 'column_span' => 6]),
                    T::field('company', 'text', 'Company', ['column_span' => 6]),
                    T::field('email', 'email', 'Email', ['required' => true, 'column_span' => 6]),
                    T::field('phone', 'phone', 'Phone', ['required' => true, 'column_span' => 6]),
                    T::field('project_address', 'textarea', 'Project address', ['required' => true, 'column_span' => 12]),
                    T::field('project_type', 'select', 'Project type', ['required' => true, 'column_span' => 6, 'options' => [
                        ['label' => 'New build', 'value' => 'new'],
                        ['label' => 'Renovation', 'value' => 'reno'],
                        ['label' => 'Addition', 'value' => 'addition'],
                    ]]),
                    T::field('timeline', 'text', 'Desired timeline', ['column_span' => 6]),
                    T::field('scope', 'textarea', 'Scope of work', ['required' => true, 'column_span' => 12]),
                    T::field('plans', 'file', 'Plans or drawings', ['column_span' => 12]),
                ]),
            ],
        );
    }

  /**
   * @return array<string, mixed>
   */
    protected static function repairQuote(): array
    {
        return T::make(
            'repair-quote',
            'Repair Quote',
            'Request repair estimate with photos and urgency.',
            'quotation-forms',
            [
                T::page('Repair', [
                    T::field('contact_name', 'text', 'Name', ['required' => true, 'column_span' => 6]),
                    T::field('phone', 'phone', 'Phone', ['required' => true, 'column_span' => 6]),
                    T::field('item_or_system', 'text', 'Item / system to repair', ['required' => true, 'column_span' => 12]),
                    T::field('issue_description', 'textarea', 'Describe the issue', ['required' => true, 'column_span' => 12]),
                    T::field('urgency', 'radio', 'Urgency', ['required' => true, 'column_span' => 12, 'options' => [
                        ['label' => 'Routine', 'value' => 'routine'],
                        ['label' => 'Soon', 'value' => 'soon'],
                        ['label' => 'Emergency', 'value' => 'emergency'],
                    ]]),
                    T::field('photos', 'file', 'Photos', ['column_span' => 12]),
                ]),
            ],
        );
    }

  /**
   * @return array<string, mixed>
   */
    protected static function serviceQuote(): array
    {
        return T::make(
            'service-quote',
            'Service Quote',
            'Request pricing for professional services.',
            'quotation-forms',
            [
                T::page('Service', [
                    T::field('contact_name', 'text', 'Contact name', ['required' => true, 'column_span' => 6]),
                    T::field('email', 'email', 'Email', ['required' => true, 'column_span' => 6]),
                    T::field('service_needed', 'select', 'Service', ['required' => true, 'column_span' => 12, 'options' => [
                        ['label' => 'Installation', 'value' => 'install'],
                        ['label' => 'Maintenance contract', 'value' => 'maint'],
                        ['label' => 'One-time service', 'value' => 'onetime'],
                    ]]),
                    T::field('location', 'text', 'Service location', ['required' => true, 'column_span' => 12]),
                    T::field('preferred_date', 'date', 'Preferred start date', ['column_span' => 6]),
                    T::field('budget_range', 'select', 'Budget range', ['column_span' => 6, 'options' => [
                        ['label' => 'Under $1,000', 'value' => 'under1k'],
                        ['label' => '$1,000 – $5,000', 'value' => '1k-5k'],
                        ['label' => '$5,000+', 'value' => '5k+'],
                    ]]),
                    T::field('details', 'textarea', 'Project details', ['required' => true, 'column_span' => 12]),
                ]),
            ],
        );
    }

  /**
   * @return array<string, mixed>
   */
    protected static function consultingQuote(): array
    {
        return T::make(
            'consulting-quote',
            'Consulting Quote',
            'Engagement scoping for consulting services.',
            'quotation-forms',
            [
                T::page('Engagement', [
                    T::field('contact_name', 'text', 'Contact name', ['required' => true, 'column_span' => 6]),
                    T::field('organization', 'text', 'Organization', ['required' => true, 'column_span' => 6]),
                    T::field('email', 'email', 'Email', ['required' => true, 'column_span' => 6]),
                    T::field('phone', 'phone', 'Phone', ['column_span' => 6]),
                    T::field('consulting_area', 'multi_select', 'Areas of interest', ['required' => true, 'column_span' => 12, 'options' => [
                        ['label' => 'Strategy', 'value' => 'strategy'],
                        ['label' => 'Operations', 'value' => 'ops'],
                        ['label' => 'Technology', 'value' => 'tech'],
                        ['label' => 'HR / org design', 'value' => 'hr'],
                    ]]),
                    T::field('engagement_length', 'select', 'Expected engagement', ['column_span' => 6, 'options' => [
                        ['label' => '1–2 weeks', 'value' => 'short'],
                        ['label' => '1–3 months', 'value' => 'medium'],
                        ['label' => '6+ months', 'value' => 'long'],
                    ]]),
                    T::field('objectives', 'textarea', 'Objectives & deliverables', ['required' => true, 'column_span' => 12, 'meta' => ['use_editor' => true]]),
                ]),
            ],
        );
    }

  /**
   * @return array<string, mixed>
   */
    protected static function quoteRequestRfq(): array
    {
        return T::make(
            'quote-request-rfq',
            'Quote Request (RFQ)',
            'Formal request for quotation with line items.',
            'quotation-forms',
            [
                T::page('RFQ', [
                    T::content('heading', ['text' => 'Request for quotation']),
                    T::field('company_name', 'text', 'Company name', ['required' => true, 'column_span' => 6]),
                    T::field('contact_name', 'text', 'Contact name', ['required' => true, 'column_span' => 6]),
                    T::field('email', 'email', 'Email', ['required' => true, 'column_span' => 6]),
                    T::field('rfq_number', 'text', 'RFQ reference #', ['column_span' => 6]),
                    T::field('response_due', 'date', 'Quote due by', ['required' => true, 'column_span' => 6]),
                    T::field('delivery_location', 'text', 'Delivery location', ['column_span' => 6]),
                    T::field('line_items', 'textarea', 'Items requested', ['required' => true, 'column_span' => 12, 'placeholder' => 'SKU, description, quantity']),
                    T::field('terms', 'textarea', 'Terms & conditions', ['column_span' => 12]),
                    T::field('specs', 'file', 'Specifications attachment', ['column_span' => 12]),
                ]),
            ],
            'single',
            'core',
            'heroicon-o-document-magnifying-glass',
            'RFQ submitted — vendors will respond by the due date.',
        );
    }
}
