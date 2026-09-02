<?php

namespace Spiggle\FormBuilder\Support\Templates;

use Spiggle\FormBuilder\Support\TemplateBuilder as T;

class RealEstateFormTemplates
{
    /**
     * @return list<array<string, mixed>>
     */
    public static function all(): array
    {
        return [
            self::propertyManagement(),
            self::cleaningRequest(),
            self::rentLeaseAgreement(),
        ];
    }

  /**
   * @return array<string, mixed>
   */
    protected static function propertyManagement(): array
    {
        return T::make(
            'property-management',
            'Property Management Inquiry',
            'Owner or tenant inquiry for property management services.',
            'real-estate-property',
            [
                T::page('Inquiry', [
                    T::field('contact_name', 'text', 'Contact name', ['required' => true, 'column_span' => 6]),
                    T::field('email', 'email', 'Email', ['required' => true, 'column_span' => 6]),
                    T::field('phone', 'phone', 'Phone', ['required' => true, 'column_span' => 6]),
                    T::field('role', 'radio', 'I am a', ['required' => true, 'column_span' => 6, 'options' => [
                        ['label' => 'Property owner', 'value' => 'owner'],
                        ['label' => 'Tenant', 'value' => 'tenant'],
                        ['label' => 'Prospective owner', 'value' => 'prospect'],
                    ]]),
                    T::field('property_address', 'textarea', 'Property address', ['required' => true, 'column_span' => 12]),
                    T::field('property_type', 'select', 'Property type', ['column_span' => 6, 'options' => [
                        ['label' => 'Single family', 'value' => 'sfh'],
                        ['label' => 'Multi-family', 'value' => 'mf'],
                        ['label' => 'Commercial', 'value' => 'commercial'],
                    ]]),
                    T::field('units', 'number', 'Number of units', ['column_span' => 6]),
                    T::field('message', 'textarea', 'How can we help?', ['required' => true, 'column_span' => 12]),
                ]),
            ],
        );
    }

  /**
   * @return array<string, mixed>
   */
    protected static function cleaningRequest(): array
    {
        return T::make(
            'cleaning-request',
            'Cleaning Request',
            'Schedule property cleaning with access and scope details.',
            'real-estate-property',
            [
                T::page('Cleaning', [
                    T::field('requester_name', 'text', 'Requester', ['required' => true, 'column_span' => 6]),
                    T::field('property_address', 'textarea', 'Property address', ['required' => true, 'column_span' => 12]),
                    T::field('cleaning_type', 'select', 'Cleaning type', ['required' => true, 'column_span' => 6, 'options' => [
                        ['label' => 'Standard turnover', 'value' => 'turnover'],
                        ['label' => 'Deep clean', 'value' => 'deep'],
                        ['label' => 'Move-out', 'value' => 'moveout'],
                    ]]),
                    T::field('preferred_date', 'date', 'Preferred date', ['required' => true, 'column_span' => 6]),
                    T::field('bedrooms', 'number', 'Bedrooms', ['column_span' => 6]),
                    T::field('bathrooms', 'number', 'Bathrooms', ['column_span' => 6]),
                    T::field('access_instructions', 'textarea', 'Access instructions', ['required' => true, 'column_span' => 12]),
                    T::field('supplies_provided', 'toggle', 'Cleaning supplies on site', ['column_span' => 12]),
                ]),
            ],
        );
    }

  /**
   * @return array<string, mixed>
   */
    protected static function rentLeaseAgreement(): array
    {
        return T::make(
            'rent-lease-agreement',
            'Rent / Lease Agreement',
            'Residential lease application with terms and tenant details.',
            'real-estate-property',
            [
                T::page('Tenant', [
                    T::field('tenant_name', 'text', 'Tenant name', ['required' => true, 'column_span' => 6]),
                    T::field('co_tenant_name', 'text', 'Co-tenant name', ['column_span' => 6]),
                    T::field('email', 'email', 'Email', ['required' => true, 'column_span' => 6]),
                    T::field('phone', 'phone', 'Phone', ['required' => true, 'column_span' => 6]),
                    T::field('current_address', 'textarea', 'Current address', ['required' => true, 'column_span' => 12]),
                ]),
                T::page('Lease terms', [
                    T::field('property_address', 'textarea', 'Rental property address', ['required' => true, 'column_span' => 12]),
                    T::field('lease_start', 'date', 'Lease start', ['required' => true, 'column_span' => 6]),
                    T::field('lease_end', 'date', 'Lease end', ['required' => true, 'column_span' => 6]),
                    T::field('monthly_rent', 'number', 'Monthly rent', ['required' => true, 'column_span' => 6]),
                    T::field('security_deposit', 'number', 'Security deposit', ['required' => true, 'column_span' => 6]),
                    T::field('pets_allowed', 'toggle', 'Pets allowed', ['column_span' => 6]),
                    T::field('parking_spaces', 'number', 'Parking spaces included', ['column_span' => 6]),
                    T::content('paragraph', ['text' => 'By submitting, tenant acknowledges receipt of lease terms and agrees to background and credit check.']),
                    T::field('lease_ack', 'toggle', 'I agree to the lease terms', ['required' => true, 'column_span' => 12]),
                ]),
            ],
            'tabs',
            'pro',
            'heroicon-o-home',
            'Lease application received — we will contact you to schedule signing.',
        );
    }
}
