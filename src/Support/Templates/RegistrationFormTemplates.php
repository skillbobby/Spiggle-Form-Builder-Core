<?php

namespace Spiggle\FormBuilder\Support\Templates;

use Spiggle\FormBuilder\Support\TemplateBuilder as T;
use Spiggle\FormBuilder\Support\TemplateChrome;

class RegistrationFormTemplates
{
    /**
     * @return list<array<string, mixed>>
     */
    public static function all(): array
    {
        return [
            self::workshopRegistration(),
            self::conferenceRegistration(),
            self::eventRegistration(),
            self::schoolCourseRegistration(),
            self::rsvp(),
            self::volunteerSignup(),
        ];
    }

  /**
   * @return array<string, mixed>
   */
    protected static function workshopRegistration(): array
    {
        return T::make(
            'workshop-registration',
            'Workshop Registration',
            'Hands-on session signup with skill level and materials.',
            'registration-forms',
            [
                T::page('Attendee', [
                    T::field('attendee_name', 'text', 'Full name', ['required' => true, 'column_span' => 6]),
                    T::field('attendee_email', 'email', 'Email', ['required' => true, 'column_span' => 6]),
                    T::field('workshop', 'select', 'Workshop', ['required' => true, 'column_span' => 12, 'options' => [
                        ['label' => 'Intro to Laravel', 'value' => 'laravel'],
                        ['label' => 'Filament deep dive', 'value' => 'filament'],
                        ['label' => 'Livewire patterns', 'value' => 'livewire'],
                    ]]),
                    T::field('skill_level', 'radio', 'Experience level', ['required' => true, 'column_span' => 12, 'options' => [
                        ['label' => 'Beginner', 'value' => 'beginner'],
                        ['label' => 'Intermediate', 'value' => 'intermediate'],
                        ['label' => 'Advanced', 'value' => 'advanced'],
                    ]]),
                    T::field('laptop', 'toggle', 'Bringing a laptop', ['column_span' => 12]),
                    T::field('accessibility', 'textarea', 'Accessibility needs', ['column_span' => 12]),
                ]),
            ],
        );
    }

  /**
   * @return array<string, mixed>
   */
    protected static function conferenceRegistration(): array
    {
        return T::make(
            'conference-registration',
            'Conference Registration',
            'Multi-day conference with pass type and sessions.',
            'registration-forms',
            [
                T::page('Profile', [
                    T::field('attendee_name', 'text', 'Full name', ['required' => true, 'column_span' => 6]),
                    T::field('attendee_email', 'email', 'Email', ['required' => true, 'column_span' => 6]),
                    T::field('job_title', 'text', 'Job title', ['column_span' => 6]),
                    T::field('organization', 'text', 'Organization', ['column_span' => 6]),
                ]),
                T::page('Pass & sessions', [
                    T::field('pass_type', 'radio', 'Pass type', ['required' => true, 'column_span' => 12, 'options' => [
                        ['label' => 'Full conference — $499', 'value' => 'full'],
                        ['label' => 'Single day — $199', 'value' => 'day'],
                        ['label' => 'Virtual only — $99', 'value' => 'virtual'],
                    ]]),
                    T::field('sessions', 'multi_select', 'Breakout sessions', ['column_span' => 12, 'options' => [
                        ['label' => 'Keynote day 1', 'value' => 'keynote1'],
                        ['label' => 'Workshop track A', 'value' => 'track_a'],
                        ['label' => 'Workshop track B', 'value' => 'track_b'],
                        ['label' => 'Networking lunch', 'value' => 'lunch'],
                    ]]),
                    T::field('dietary', 'multi_select', 'Meal preferences', ['column_span' => 12, 'options' => [
                        ['label' => 'Standard', 'value' => 'standard'],
                        ['label' => 'Vegetarian', 'value' => 'veg'],
                        ['label' => 'Vegan', 'value' => 'vegan'],
                        ['label' => 'Gluten-free', 'value' => 'gf'],
                    ]]),
                ]),
            ],
            'wizard',
            'pro',
            'heroicon-o-academic-cap',
            'You are registered for the conference. Check your inbox for your badge.',
            TemplateChrome::conferenceShowcase(),
        );
    }

  /**
   * @return array<string, mixed>
   */
    protected static function eventRegistration(): array
    {
        return T::make(
            'event-registration',
            'Event Registration',
            'Wizard: attendee details, tickets, then extras.',
            'registration-forms',
            [
                T::page('Attendee', [
                    T::field('attendee_name', 'text', 'Name', ['required' => true, 'column_span' => 6]),
                    T::field('attendee_email', 'email', 'Email', ['required' => true, 'column_span' => 6]),
                    T::field('company', 'text', 'Company', ['column_span' => 12]),
                ], 'Who is coming?'),
                T::page('Tickets', [
                    T::field('ticket_type', 'radio', 'Ticket', ['required' => true, 'column_span' => 12, 'options' => [
                        ['label' => 'General admission — $49', 'value' => 'ga'],
                        ['label' => 'Workshop pass — $129', 'value' => 'workshop'],
                        ['label' => 'VIP — $199', 'value' => 'vip'],
                    ]]),
                    T::field('quantity', 'number', 'Quantity', ['required' => true, 'column_span' => 6, 'validation_rules' => ['min:1', 'max:8']]),
                ]),
                T::page('Extras', [
                    T::field('dietary', 'multi_select', 'Dietary needs', ['column_span' => 12, 'options' => [
                        ['label' => 'Vegetarian', 'value' => 'veg'],
                        ['label' => 'Vegan', 'value' => 'vegan'],
                        ['label' => 'Gluten-free', 'value' => 'gf'],
                        ['label' => 'None', 'value' => 'none'],
                    ]]),
                    T::field('notes', 'textarea', 'Notes for the host', ['column_span' => 12]),
                ]),
            ],
            'wizard',
            'pro',
            'heroicon-o-calendar-days',
            'You are registered. Check your inbox for a confirmation.',
            TemplateChrome::eventShowcase(),
        );
    }

  /**
   * @return array<string, mixed>
   */
    protected static function schoolCourseRegistration(): array
    {
        return T::make(
            'school-course-registration',
            'School / Course Registration',
            'Enroll students with guardian contact and course selection.',
            'registration-forms',
            [
                T::page('Student', [
                    T::field('student_name', 'text', 'Student name', ['required' => true, 'column_span' => 6]),
                    T::field('date_of_birth', 'date', 'Date of birth', ['required' => true, 'column_span' => 6]),
                    T::field('grade_level', 'select', 'Grade / level', ['required' => true, 'column_span' => 6, 'options' => [
                        ['label' => 'Grade 9', 'value' => '9'], ['label' => 'Grade 10', 'value' => '10'],
                        ['label' => 'Grade 11', 'value' => '11'], ['label' => 'Grade 12', 'value' => '12'],
                        ['label' => 'Undergraduate', 'value' => 'undergrad'],
                    ]]),
                    T::field('student_id', 'text', 'Student ID', ['column_span' => 6]),
                ]),
                T::page('Courses', [
                    T::field('term', 'select', 'Term', ['required' => true, 'column_span' => 6, 'options' => [
                        ['label' => 'Fall 2026', 'value' => 'fall2026'],
                        ['label' => 'Spring 2027', 'value' => 'spring2027'],
                    ]]),
                    T::field('courses', 'multi_select', 'Courses', ['required' => true, 'column_span' => 12, 'options' => [
                        ['label' => 'Algebra II', 'value' => 'algebra'],
                        ['label' => 'English Literature', 'value' => 'english'],
                        ['label' => 'Computer Science', 'value' => 'cs'],
                        ['label' => 'Art Studio', 'value' => 'art'],
                    ]]),
                    T::field('guardian_name', 'text', 'Parent / guardian name', ['required' => true, 'column_span' => 6]),
                    T::field('guardian_email', 'email', 'Guardian email', ['required' => true, 'column_span' => 6]),
                    T::field('guardian_phone', 'phone', 'Guardian phone', ['column_span' => 6]),
                ]),
            ],
            'tabs',
            'pro',
            'heroicon-o-book-open',
            'Enrollment submitted — you will receive a schedule confirmation.',
            TemplateChrome::schoolTabsShowcase(),
        );
    }

  /**
   * @return array<string, mixed>
   */
    protected static function rsvp(): array
    {
        return T::make(
            'rsvp',
            'RSVP',
            'Quick event attendance response with guest count.',
            'registration-forms',
            [
                T::page('Response', [
                    T::field('guest_name', 'text', 'Your name', ['required' => true, 'column_span' => 6]),
                    T::field('guest_email', 'email', 'Email', ['required' => true, 'column_span' => 6]),
                    T::field('attending', 'radio', 'Will you attend?', ['required' => true, 'column_span' => 12, 'options' => [
                        ['label' => 'Joyfully accepts', 'value' => 'yes'],
                        ['label' => 'Regretfully declines', 'value' => 'no'],
                        ['label' => 'Maybe', 'value' => 'maybe'],
                    ]]),
                    T::field('plus_ones', 'number', 'Additional guests', ['column_span' => 6, 'validation_rules' => ['min:0', 'max:5']]),
                    T::field('meal_choice', 'select', 'Meal preference', ['column_span' => 6, 'options' => [
                        ['label' => 'Chicken', 'value' => 'chicken'],
                        ['label' => 'Fish', 'value' => 'fish'],
                        ['label' => 'Vegetarian', 'value' => 'veg'],
                    ]]),
                    T::field('song_request', 'text', 'Song request', ['column_span' => 12, 'placeholder' => 'Optional']),
                ]),
            ],
            'single',
            'core',
            'heroicon-o-envelope-open',
            'Thanks for your RSVP!',
        );
    }

  /**
   * @return array<string, mixed>
   */
    protected static function volunteerSignup(): array
    {
        return T::make(
            'volunteer-signup',
            'Volunteer Signup',
            'Accordion sections for availability and skills.',
            'registration-forms',
            [
                T::page('About you', [
                    T::field('volunteer_name', 'text', 'Name', ['required' => true, 'column_span' => 6]),
                    T::field('volunteer_email', 'email', 'Email', ['required' => true, 'column_span' => 6]),
                ]),
                T::page('Availability', [
                    T::field('days', 'multi_select', 'Days you can help', ['required' => true, 'column_span' => 12, 'options' => [
                        ['label' => 'Saturday', 'value' => 'sat'],
                        ['label' => 'Sunday', 'value' => 'sun'],
                        ['label' => 'Weeknights', 'value' => 'weeknight'],
                    ]]),
                ]),
                T::page('Skills', [
                    T::field('volunteer_skills', 'tags', 'Skills', ['column_span' => 12]),
                ]),
            ],
            'accordion',
            'pro',
            'heroicon-o-heart',
            'Thanks — we will email shift details.',
        );
    }
}
