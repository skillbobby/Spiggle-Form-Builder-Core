<?php

namespace Spiggle\FormBuilder\Support\Templates;

use Spiggle\FormBuilder\Support\TemplateBuilder as T;

class ManagementFormTemplates
{
    /**
     * @return list<array<string, mixed>>
     */
    public static function all(): array
    {
        return [
            self::facilityRequest(),
            self::fleetLog(),
            self::supplierRegistration(),
            self::equipmentCheckout(),
        ];
    }

  /**
   * @return array<string, mixed>
   */
    protected static function facilityRequest(): array
    {
        return T::make(
            'facility-request',
            'Facility Request',
            'Request facility access, room setup, or building services.',
            'management-operations',
            [
                T::page('Request', [
                    T::field('requester_name', 'text', 'Requester', ['required' => true, 'column_span' => 6]),
                    T::field('building', 'select', 'Building', ['required' => true, 'column_span' => 6, 'options' => [
                        ['label' => 'Main campus', 'value' => 'main'],
                        ['label' => 'North wing', 'value' => 'north'],
                        ['label' => 'Warehouse', 'value' => 'warehouse'],
                    ]]),
                    T::field('room', 'text', 'Room / area', ['required' => true, 'column_span' => 6]),
                    T::field('request_type', 'select', 'Request type', ['required' => true, 'column_span' => 6, 'options' => [
                        ['label' => 'Room booking', 'value' => 'booking'],
                        ['label' => 'Access badge', 'value' => 'badge'],
                        ['label' => 'Setup / teardown', 'value' => 'setup'],
                        ['label' => 'Cleaning', 'value' => 'cleaning'],
                    ]]),
                    T::field('needed_date', 'date', 'Date needed', ['required' => true, 'column_span' => 6]),
                    T::field('details', 'textarea', 'Details', ['required' => true, 'column_span' => 12]),
                ]),
            ],
        );
    }

  /**
   * @return array<string, mixed>
   */
    protected static function fleetLog(): array
    {
        return T::make(
            'fleet-log',
            'Fleet Log',
            'Vehicle usage log with mileage and fuel.',
            'management-operations',
            [
                T::page('Trip log', [
                    T::field('driver_name', 'text', 'Driver', ['required' => true, 'column_span' => 6]),
                    T::field('vehicle_id', 'text', 'Vehicle ID / plate', ['required' => true, 'column_span' => 6]),
                    T::field('trip_date', 'date', 'Date', ['required' => true, 'column_span' => 6]),
                    T::field('odometer_start', 'number', 'Odometer start', ['required' => true, 'column_span' => 6]),
                    T::field('odometer_end', 'number', 'Odometer end', ['required' => true, 'column_span' => 6]),
                    T::field('purpose', 'text', 'Trip purpose', ['required' => true, 'column_span' => 6]),
                    T::field('fuel_added', 'number', 'Fuel added (gallons)', ['column_span' => 6]),
                    T::field('notes', 'textarea', 'Notes / incidents', ['column_span' => 12]),
                ]),
            ],
        );
    }

  /**
   * @return array<string, mixed>
   */
    protected static function supplierRegistration(): array
    {
        return T::make(
            'supplier-registration',
            'Supplier Registration',
            'Onboard a new vendor with tax and banking details.',
            'management-operations',
            [
                T::page('Vendor', [
                    T::field('company_name', 'text', 'Company name', ['required' => true, 'column_span' => 6]),
                    T::field('contact_name', 'text', 'Primary contact', ['required' => true, 'column_span' => 6]),
                    T::field('email', 'email', 'Email', ['required' => true, 'column_span' => 6]),
                    T::field('phone', 'phone', 'Phone', ['required' => true, 'column_span' => 6]),
                    T::field('tax_id', 'text', 'Tax ID / VAT number', ['required' => true, 'column_span' => 6]),
                    T::field('payment_terms', 'select', 'Payment terms', ['column_span' => 6, 'options' => [
                        ['label' => 'Net 30', 'value' => 'net30'],
                        ['label' => 'Net 60', 'value' => 'net60'],
                        ['label' => 'Due on receipt', 'value' => 'due'],
                    ]]),
                    T::field('services', 'tags', 'Products / services provided', ['column_span' => 12]),
                    T::field('w9', 'file', 'W-9 or tax form', ['column_span' => 12]),
                ]),
            ],
        );
    }

  /**
   * @return array<string, mixed>
   */
    protected static function equipmentCheckout(): array
    {
        return T::make(
            'equipment-checkout',
            'Equipment Checkout',
            'Check out tools or equipment with condition notes.',
            'management-operations',
            [
                T::page('Checkout', [
                    T::field('borrower_name', 'text', 'Borrower', ['required' => true, 'column_span' => 6]),
                    T::field('department', 'text', 'Department', ['required' => true, 'column_span' => 6]),
                    T::field('equipment', 'select', 'Equipment', ['required' => true, 'column_span' => 12, 'options' => [
                        ['label' => 'Laptop — #L-1042', 'value' => 'l1042'],
                        ['label' => 'Projector — #P-008', 'value' => 'p008'],
                        ['label' => 'Power drill — #T-221', 'value' => 't221'],
                    ]]),
                    T::field('checkout_date', 'date', 'Checkout date', ['required' => true, 'column_span' => 6]),
                    T::field('return_date', 'date', 'Expected return', ['required' => true, 'column_span' => 6]),
                    T::field('condition_out', 'radio', 'Condition at checkout', ['required' => true, 'column_span' => 12, 'options' => [
                        ['label' => 'Excellent', 'value' => 'excellent'],
                        ['label' => 'Good', 'value' => 'good'],
                        ['label' => 'Fair', 'value' => 'fair'],
                    ]]),
                    T::field('ack_responsible', 'toggle', 'I am responsible for loss or damage', ['required' => true, 'column_span' => 12]),
                ]),
            ],
        );
    }
}
