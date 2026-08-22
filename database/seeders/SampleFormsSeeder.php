<?php

namespace Spiggle\FormBuilder\Database\Seeders;

use Illuminate\Database\Seeder;
use Spiggle\FormBuilder\Models\Form;
use Spiggle\FormBuilder\Models\FormSubmission;
use Spiggle\FormBuilder\Support\SchemaNormalizer;

class SampleFormsSeeder extends Seeder
{
    public function run(): void
    {
        foreach ($this->forms() as $definition) {
            $submissions = $definition['submissions'] ?? [];
            unset($definition['submissions']);

            $form = Form::withTrashed()->updateOrCreate(
                ['slug' => $definition['slug']],
                array_merge($definition, ['deleted_at' => null])
            );

            if ($form->submissions()->exists()) {
                continue;
            }

            foreach ($submissions as $index => $data) {
                FormSubmission::query()->create([
                    'form_id' => $form->id,
                    'status' => $index === 0 ? 'reviewed' : 'new',
                    'data' => $data,
                    'meta' => ['source' => 'seeder'],
                    'created_at' => now()->subDays($index)->subHours($index + 1),
                ]);
            }
        }
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    protected function forms(): array
    {
        return [
            [
                'name' => 'Contact Us',
                'slug' => 'contact-us',
                'base_path' => 'contact-us',
                'description' => 'A simple contact form for website visitors.',
                'container_type' => 'single',
                'is_published' => true,
                'is_active' => true,
                'success_message' => 'Thanks for writing in — we typically reply within one business day.',
                'schema' => SchemaNormalizer::normalize([
                    [
                        'label' => 'Message',
                        'fields' => [
                            ['name' => 'full_name', 'type' => 'text', 'label' => 'Full name', 'required' => true, 'column_span' => 6, 'placeholder' => 'Ada Lovelace'],
                            ['name' => 'email', 'type' => 'email', 'label' => 'Email', 'required' => true, 'column_span' => 6, 'validation_rules' => ['email']],
                            ['name' => 'phone', 'type' => 'phone', 'label' => 'Phone', 'column_span' => 6],
                            ['name' => 'topic', 'type' => 'select', 'label' => 'Topic', 'required' => true, 'column_span' => 6, 'options' => [
                                ['label' => 'General', 'value' => 'general'],
                                ['label' => 'Sales', 'value' => 'sales'],
                                ['label' => 'Support', 'value' => 'support'],
                            ]],
                            ['name' => 'message', 'type' => 'textarea', 'label' => 'How can we help?', 'required' => true, 'column_span' => 12, 'meta' => ['rows' => 5]],
                            ['name' => 'marketing_opt_in', 'type' => 'toggle', 'label' => 'Send me product updates', 'column_span' => 12],
                        ],
                    ],
                ]),
                'submissions' => [
                    ['full_name' => 'Maya Chen', 'email' => 'maya@example.com', 'phone' => '+1 555 0101', 'topic' => 'sales', 'message' => 'We would like a demo of the form builder for our membership site.', 'marketing_opt_in' => true],
                    ['full_name' => 'Jonah Price', 'email' => 'jonah@example.com', 'phone' => '+1 555 0144', 'topic' => 'support', 'message' => 'The public form on mobile wraps labels oddly on our staging site.', 'marketing_opt_in' => false],
                    ['full_name' => 'Priya Shah', 'email' => 'priya@example.org', 'topic' => 'general', 'message' => 'Do you support wizard layouts with draft saving between pages?', 'marketing_opt_in' => true],
                ],
            ],
            [
                'name' => 'Event Registration',
                'slug' => 'event-registration',
                'base_path' => 'event-registration',
                'description' => 'Wizard: attendee details, tickets, then extras.',
                'container_type' => 'wizard',
                'is_published' => true,
                'is_active' => true,
                'success_message' => 'You are registered. Check your inbox for a confirmation.',
                'schema' => SchemaNormalizer::normalize([
                    [
                        'label' => 'Attendee',
                        'description' => 'Who is coming?',
                        'fields' => [
                            ['name' => 'attendee_name', 'type' => 'text', 'label' => 'Name', 'required' => true, 'column_span' => 6],
                            ['name' => 'attendee_email', 'type' => 'email', 'label' => 'Email', 'required' => true, 'column_span' => 6],
                            ['name' => 'company', 'type' => 'text', 'label' => 'Company', 'column_span' => 12],
                        ],
                    ],
                    [
                        'label' => 'Tickets',
                        'fields' => [
                            ['name' => 'ticket_type', 'type' => 'radio', 'label' => 'Ticket', 'required' => true, 'column_span' => 12, 'options' => [
                                ['label' => 'General admission — $49', 'value' => 'ga'],
                                ['label' => 'Workshop pass — $129', 'value' => 'workshop'],
                                ['label' => 'VIP — $199', 'value' => 'vip'],
                            ]],
                            ['name' => 'quantity', 'type' => 'number', 'label' => 'Quantity', 'required' => true, 'column_span' => 6, 'validation_rules' => ['min:1', 'max:8']],
                        ],
                    ],
                    [
                        'label' => 'Extras',
                        'fields' => [
                            ['name' => 'dietary', 'type' => 'multi_select', 'label' => 'Dietary needs', 'column_span' => 12, 'options' => [
                                ['label' => 'Vegetarian', 'value' => 'veg'],
                                ['label' => 'Vegan', 'value' => 'vegan'],
                                ['label' => 'Gluten-free', 'value' => 'gf'],
                                ['label' => 'None', 'value' => 'none'],
                            ]],
                            ['name' => 'notes', 'type' => 'textarea', 'label' => 'Notes for the host', 'column_span' => 12],
                        ],
                    ],
                ]),
                'submissions' => [
                    ['attendee_name' => 'Sam Rivera', 'attendee_email' => 'sam@example.com', 'company' => 'Northwind', 'ticket_type' => 'workshop', 'quantity' => 2, 'dietary' => ['veg'], 'notes' => 'Need aisle seats.'],
                    ['attendee_name' => 'Lee Okonkwo', 'attendee_email' => 'lee@example.com', 'company' => 'Contoso', 'ticket_type' => 'vip', 'quantity' => 1, 'dietary' => ['none'], 'notes' => ''],
                ],
            ],
            [
                'name' => 'Job Application',
                'slug' => 'job-application',
                'base_path' => 'job-application',
                'description' => 'Tabbed application: profile, experience, links.',
                'container_type' => 'tabs',
                'is_published' => true,
                'is_active' => true,
                'success_message' => 'Application received. We will be in touch if there is a fit.',
                'schema' => SchemaNormalizer::normalize([
                    [
                        'label' => 'Profile',
                        'fields' => [
                            ['name' => 'applicant_name', 'type' => 'text', 'label' => 'Name', 'required' => true, 'column_span' => 6],
                            ['name' => 'applicant_email', 'type' => 'email', 'label' => 'Email', 'required' => true, 'column_span' => 6],
                            ['name' => 'role', 'type' => 'select', 'label' => 'Role', 'required' => true, 'column_span' => 12, 'options' => [
                                ['label' => 'Product designer', 'value' => 'design'],
                                ['label' => 'Laravel engineer', 'value' => 'eng'],
                                ['label' => 'Customer success', 'value' => 'cs'],
                            ]],
                        ],
                    ],
                    [
                        'label' => 'Experience',
                        'fields' => [
                            ['name' => 'years', 'type' => 'number', 'label' => 'Years of experience', 'required' => true, 'column_span' => 6],
                            ['name' => 'skills', 'type' => 'tags', 'label' => 'Skills', 'column_span' => 12, 'options' => [
                                ['label' => 'Laravel', 'value' => 'laravel'],
                                ['label' => 'Livewire', 'value' => 'livewire'],
                                ['label' => 'Filament', 'value' => 'filament'],
                            ]],
                            ['name' => 'summary', 'type' => 'textarea', 'label' => 'Summary', 'required' => true, 'column_span' => 12, 'meta' => ['use_editor' => true]],
                        ],
                    ],
                    [
                        'label' => 'Links',
                        'fields' => [
                            ['name' => 'portfolio', 'type' => 'url', 'label' => 'Portfolio / GitHub', 'column_span' => 12],
                            ['name' => 'available', 'type' => 'date', 'label' => 'Available from', 'column_span' => 6],
                        ],
                    ],
                ]),
                'submissions' => [
                    ['applicant_name' => 'Chris Patel', 'applicant_email' => 'chris@example.com', 'role' => 'eng', 'years' => 6, 'skills' => ['laravel', 'filament'], 'summary' => 'Shipped Filament plugins and Livewire storefronts.', 'portfolio' => 'https://github.com/example', 'available' => '2026-09-01'],
                ],
            ],
            [
                'name' => 'Customer Feedback',
                'slug' => 'customer-feedback',
                'base_path' => 'customer-feedback',
                'description' => 'Paged survey with a draft saved between pages.',
                'container_type' => 'pages',
                'is_published' => true,
                'is_active' => true,
                'success_message' => 'Thank you for the feedback.',
                'schema' => SchemaNormalizer::normalize([
                    [
                        'label' => 'Rating',
                        'fields' => [
                            ['name' => 'nps', 'type' => 'select', 'label' => 'How likely are you to recommend us?', 'required' => true, 'column_span' => 12, 'options' => collect(range(0, 10))->map(fn ($n) => ['label' => (string) $n, 'value' => (string) $n])->all()],
                            ['name' => 'product', 'type' => 'select', 'label' => 'Product', 'required' => true, 'column_span' => 12, 'options' => [
                                ['label' => 'Dynamic Fields', 'value' => 'dynamic-fields'],
                                ['label' => 'Form Builder', 'value' => 'form-builder'],
                            ]],
                        ],
                    ],
                    [
                        'label' => 'Comments',
                        'fields' => [
                            ['name' => 'what_worked', 'type' => 'textarea', 'label' => 'What worked well?', 'column_span' => 12],
                            ['name' => 'what_to_improve', 'type' => 'textarea', 'label' => 'What should we improve?', 'column_span' => 12],
                        ],
                    ],
                ]),
                'submissions' => [
                    ['nps' => '9', 'product' => 'form-builder', 'what_worked' => 'The collapsed repeater labels finally make the builder usable.', 'what_to_improve' => 'A palette of prebuilt field groups would help.'],
                    ['nps' => '7', 'product' => 'dynamic-fields', 'what_worked' => 'File fields on User just work.', 'what_to_improve' => 'More examples for conditional visibility.'],
                ],
            ],
            [
                'name' => 'Volunteer Signup',
                'slug' => 'volunteer-signup',
                'base_path' => 'volunteer-signup',
                'description' => 'Accordion sections stay listed so you can scan topics and open only what you need.',
                'container_type' => 'accordion',
                'is_published' => true,
                'is_active' => true,
                'success_message' => 'Thanks — we will email shift details.',
                'schema' => SchemaNormalizer::normalize([
                    [
                        'label' => 'About you',
                        'fields' => [
                            ['name' => 'volunteer_name', 'type' => 'text', 'label' => 'Name', 'required' => true, 'column_span' => 6],
                            ['name' => 'volunteer_email', 'type' => 'email', 'label' => 'Email', 'required' => true, 'column_span' => 6],
                        ],
                    ],
                    [
                        'label' => 'Availability',
                        'fields' => [
                            ['name' => 'days', 'type' => 'multi_select', 'label' => 'Days you can help', 'required' => true, 'column_span' => 12, 'options' => [
                                ['label' => 'Saturday', 'value' => 'sat'],
                                ['label' => 'Sunday', 'value' => 'sun'],
                                ['label' => 'Weeknights', 'value' => 'weeknight'],
                            ]],
                        ],
                    ],
                    [
                        'label' => 'Skills',
                        'fields' => [
                            ['name' => 'volunteer_skills', 'type' => 'tags', 'label' => 'Skills', 'column_span' => 12],
                        ],
                    ],
                ]),
                'submissions' => [
                    ['volunteer_name' => 'Alex Kim', 'volunteer_email' => 'alex@example.com', 'days' => ['sat', 'sun'], 'volunteer_skills' => ['First aid', 'Setup']],
                ],
            ],
        ];
    }
}
