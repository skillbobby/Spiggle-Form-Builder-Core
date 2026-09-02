<?php

namespace Spiggle\FormBuilder\Support\Templates;

use Spiggle\FormBuilder\Support\TemplateBuilder as T;
use Spiggle\FormBuilder\Support\TemplateChrome;

class BookingFormTemplates
{
    /**
     * @return list<array<string, mixed>>
     */
    public static function all(): array
    {
        return [
            self::appointmentBooking(),
            self::transportationBooking(),
            self::travelBooking(),
            self::eventBooking(),
            self::serviceBooking(),
            self::hotelReservation(),
        ];
    }

  /**
   * @return array<string, mixed>
   */
    protected static function appointmentBooking(): array
    {
        return T::make(
            'appointment-booking',
            'Appointment Booking',
            'Schedule a service appointment with preferred date and time.',
            'booking-reservation',
            [
                T::page('Appointment', [
                    T::field('client_name', 'text', 'Full name', ['required' => true, 'column_span' => 6]),
                    T::field('email', 'email', 'Email', ['required' => true, 'column_span' => 6]),
                    T::field('phone', 'phone', 'Phone', ['required' => true, 'column_span' => 6]),
                    T::field('service', 'select', 'Service', ['required' => true, 'column_span' => 6, 'options' => [
                        ['label' => 'Consultation (30 min)', 'value' => 'consult'],
                        ['label' => 'Standard appointment (60 min)', 'value' => 'standard'],
                        ['label' => 'Extended session (90 min)', 'value' => 'extended'],
                    ]]),
                    T::field('preferred_date', 'date', 'Preferred date', ['required' => true, 'column_span' => 6]),
                    T::field('preferred_time', 'select', 'Preferred time', ['required' => true, 'column_span' => 6, 'options' => [
                        ['label' => '9:00 AM', 'value' => '09:00'], ['label' => '11:00 AM', 'value' => '11:00'],
                        ['label' => '1:00 PM', 'value' => '13:00'], ['label' => '3:00 PM', 'value' => '15:00'],
                    ]]),
                    T::field('notes', 'textarea', 'Notes for provider', ['column_span' => 12]),
                ]),
            ],
            'single',
            'core',
            'heroicon-o-clock',
            'Appointment request received — we will confirm by email.',
            TemplateChrome::bookingShowcase(),
        );
    }

  /**
   * @return array<string, mixed>
   */
    protected static function transportationBooking(): array
    {
        return T::make(
            'transportation-booking',
            'Transportation Booking',
            'Book a ride or shuttle with pickup and drop-off.',
            'booking-reservation',
            [
                T::page('Trip', [
                    T::field('passenger_name', 'text', 'Passenger name', ['required' => true, 'column_span' => 6]),
                    T::field('phone', 'phone', 'Phone', ['required' => true, 'column_span' => 6]),
                    T::field('pickup_location', 'text', 'Pickup location', ['required' => true, 'column_span' => 12]),
                    T::field('dropoff_location', 'text', 'Drop-off location', ['required' => true, 'column_span' => 12]),
                    T::field('pickup_datetime', 'datetime', 'Pickup date & time', ['required' => true, 'column_span' => 6]),
                    T::field('passengers', 'number', 'Number of passengers', ['required' => true, 'column_span' => 6, 'validation_rules' => ['min:1']]),
                    T::field('vehicle_type', 'select', 'Vehicle type', ['column_span' => 6, 'options' => [
                        ['label' => 'Sedan', 'value' => 'sedan'],
                        ['label' => 'SUV', 'value' => 'suv'],
                        ['label' => 'Van', 'value' => 'van'],
                        ['label' => 'Wheelchair accessible', 'value' => 'accessible'],
                    ]]),
                    T::field('flight_number', 'text', 'Flight number', ['column_span' => 6, 'placeholder' => 'If airport transfer']),
                ]),
            ],
        );
    }

  /**
   * @return array<string, mixed>
   */
    protected static function travelBooking(): array
    {
        return T::make(
            'travel-booking',
            'Travel Booking',
            'Multi-step trip planning with flights and lodging.',
            'booking-reservation',
            [
                T::page('Traveler', [
                    T::field('traveler_name', 'text', 'Lead traveler', ['required' => true, 'column_span' => 6]),
                    T::field('email', 'email', 'Email', ['required' => true, 'column_span' => 6]),
                    T::field('travelers_count', 'number', 'Travelers', ['required' => true, 'column_span' => 6]),
                ]),
                T::page('Itinerary', [
                    T::field('destination', 'text', 'Destination', ['required' => true, 'column_span' => 6]),
                    T::field('departure_date', 'date', 'Departure date', ['required' => true, 'column_span' => 6]),
                    T::field('return_date', 'date', 'Return date', ['required' => true, 'column_span' => 6]),
                    T::field('trip_type', 'radio', 'Trip type', ['required' => true, 'column_span' => 12, 'options' => [
                        ['label' => 'Round trip', 'value' => 'round'],
                        ['label' => 'One way', 'value' => 'oneway'],
                        ['label' => 'Multi-city', 'value' => 'multi'],
                    ]]),
                    T::field('preferences', 'textarea', 'Preferences & special requests', ['column_span' => 12]),
                ]),
            ],
            'wizard',
            'pro',
            'heroicon-o-globe-alt',
            'Travel request submitted — an agent will follow up.',
        );
    }

  /**
   * @return array<string, mixed>
   */
    protected static function eventBooking(): array
    {
        return T::make(
            'event-booking',
            'Event Booking',
            'Reserve a venue or event space.',
            'booking-reservation',
            [
                T::page('Event', [
                    T::field('organizer_name', 'text', 'Organizer name', ['required' => true, 'column_span' => 6]),
                    T::field('email', 'email', 'Email', ['required' => true, 'column_span' => 6]),
                    T::field('event_name', 'text', 'Event name', ['required' => true, 'column_span' => 12]),
                    T::field('event_date', 'date', 'Event date', ['required' => true, 'column_span' => 6]),
                    T::field('guest_count', 'number', 'Expected guests', ['required' => true, 'column_span' => 6]),
                    T::field('space', 'select', 'Space requested', ['required' => true, 'column_span' => 12, 'options' => [
                        ['label' => 'Main hall', 'value' => 'hall'],
                        ['label' => 'Conference room A', 'value' => 'conf_a'],
                        ['label' => 'Outdoor terrace', 'value' => 'terrace'],
                    ]]),
                    T::field('setup', 'multi_select', 'Setup needs', ['column_span' => 12, 'options' => [
                        ['label' => 'Tables & chairs', 'value' => 'tables'],
                        ['label' => 'AV equipment', 'value' => 'av'],
                        ['label' => 'Catering kitchen', 'value' => 'catering'],
                    ]]),
                ]),
            ],
        );
    }

  /**
   * @return array<string, mixed>
   */
    protected static function serviceBooking(): array
    {
        return T::make(
            'service-booking',
            'Service Booking',
            'Book home or business services with address details.',
            'booking-reservation',
            [
                T::page('Service', [
                    T::field('client_name', 'text', 'Name', ['required' => true, 'column_span' => 6]),
                    T::field('phone', 'phone', 'Phone', ['required' => true, 'column_span' => 6]),
                    T::field('service_type', 'select', 'Service', ['required' => true, 'column_span' => 12, 'options' => [
                        ['label' => 'Cleaning', 'value' => 'cleaning'],
                        ['label' => 'HVAC maintenance', 'value' => 'hvac'],
                        ['label' => 'Landscaping', 'value' => 'landscape'],
                        ['label' => 'Plumbing', 'value' => 'plumbing'],
                    ]]),
                    T::field('service_address', 'textarea', 'Service address', ['required' => true, 'column_span' => 12]),
                    T::field('preferred_date', 'date', 'Preferred date', ['required' => true, 'column_span' => 6]),
                    T::field('access_instructions', 'textarea', 'Access instructions', ['column_span' => 12]),
                ]),
            ],
        );
    }

  /**
   * @return array<string, mixed>
   */
    protected static function hotelReservation(): array
    {
        return T::make(
            'hotel-reservation',
            'Hotel Reservation',
            'Room booking with check-in dates and guest details.',
            'booking-reservation',
            [
                T::page('Reservation', [
                    T::field('guest_name', 'text', 'Guest name', ['required' => true, 'column_span' => 6]),
                    T::field('email', 'email', 'Email', ['required' => true, 'column_span' => 6]),
                    T::field('phone', 'phone', 'Phone', ['required' => true, 'column_span' => 6]),
                    T::field('check_in', 'date', 'Check-in', ['required' => true, 'column_span' => 6]),
                    T::field('check_out', 'date', 'Check-out', ['required' => true, 'column_span' => 6]),
                    T::field('room_type', 'select', 'Room type', ['required' => true, 'column_span' => 6, 'options' => [
                        ['label' => 'Standard king', 'value' => 'standard'],
                        ['label' => 'Double queen', 'value' => 'double'],
                        ['label' => 'Suite', 'value' => 'suite'],
                    ]]),
                    T::field('guests', 'number', 'Guests', ['required' => true, 'column_span' => 6]),
                    T::field('special_requests', 'textarea', 'Special requests', ['column_span' => 12, 'placeholder' => 'Late check-in, crib, etc.']),
                ]),
            ],
            'single',
            'core',
            'heroicon-o-building-office',
            'Reservation request received — we will confirm availability shortly.',
            TemplateChrome::hotelShowcase(),
        );
    }
}
