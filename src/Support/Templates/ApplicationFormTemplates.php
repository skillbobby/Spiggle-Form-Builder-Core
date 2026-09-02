<?php

namespace Spiggle\FormBuilder\Support\Templates;

use Spiggle\FormBuilder\Support\TemplateBuilder as T;

class ApplicationFormTemplates
{
    /**
     * @return list<array<string, mixed>>
     */
    public static function all(): array
    {
        return [
            self::bankingApplication(),
            self::animalRescueApplication(),
            self::internshipApplication(),
            self::petAdoptionApplication(),
            self::staffApplication(),
            self::sponsorshipApplication(),
            self::tenantApplication(),
            self::jobApplication(),
            self::membershipApplication(),
            self::patientIntake(),
        ];
    }

  /**
   * @return array<string, mixed>
   */
    protected static function bankingApplication(): array
    {
        return T::make(
            'banking-application',
            'Banking Application',
            'Personal or business account opening request.',
            'application-forms',
            [
                T::page('Applicant', [
                    T::content('heading', ['text' => 'Account application']),
                    T::field('applicant_name', 'text', 'Legal name', ['required' => true, 'column_span' => 6]),
                    T::field('date_of_birth', 'date', 'Date of birth', ['required' => true, 'column_span' => 6]),
                    T::field('email', 'email', 'Email', ['required' => true, 'column_span' => 6]),
                    T::field('phone', 'phone', 'Phone', ['required' => true, 'column_span' => 6]),
                    T::field('account_type', 'radio', 'Account type', ['required' => true, 'column_span' => 12, 'options' => [
                        ['label' => 'Checking', 'value' => 'checking'],
                        ['label' => 'Savings', 'value' => 'savings'],
                        ['label' => 'Business checking', 'value' => 'business'],
                    ]]),
                    T::field('ssn_last4', 'text', 'SSN (last 4)', ['required' => true, 'column_span' => 6, 'placeholder' => 'XXXX']),
                    T::field('employment_status', 'select', 'Employment status', ['required' => true, 'column_span' => 6, 'options' => [
                        ['label' => 'Employed', 'value' => 'employed'],
                        ['label' => 'Self-employed', 'value' => 'self'],
                        ['label' => 'Student', 'value' => 'student'],
                        ['label' => 'Retired', 'value' => 'retired'],
                    ]]),
                    T::field('address', 'textarea', 'Home address', ['required' => true, 'column_span' => 12, 'meta' => ['rows' => 3]]),
                    T::field('id_upload', 'file', 'Government ID', ['required' => true, 'column_span' => 12]),
                ]),
            ],
        );
    }

  /**
   * @return array<string, mixed>
   */
    protected static function animalRescueApplication(): array
    {
        return T::make(
            'animal-rescue-application',
            'Animal Rescue Application',
            'Foster or adoption screening for rescue animals.',
            'application-forms',
            [
                T::page('Household', [
                    T::field('applicant_name', 'text', 'Name', ['required' => true, 'column_span' => 6]),
                    T::field('email', 'email', 'Email', ['required' => true, 'column_span' => 6]),
                    T::field('phone', 'phone', 'Phone', ['required' => true, 'column_span' => 6]),
                    T::field('housing', 'radio', 'Housing', ['required' => true, 'column_span' => 12, 'options' => [
                        ['label' => 'Own home', 'value' => 'own'],
                        ['label' => 'Rent (pets allowed)', 'value' => 'rent'],
                        ['label' => 'Other', 'value' => 'other'],
                    ]]),
                    T::field('yard', 'toggle', 'Fenced yard available', ['column_span' => 12]),
                    T::field('other_pets', 'textarea', 'Other pets in household', ['column_span' => 12]),
                    T::field('experience', 'textarea', 'Experience with rescue animals', ['required' => true, 'column_span' => 12]),
                ]),
            ],
        );
    }

  /**
   * @return array<string, mixed>
   */
    protected static function internshipApplication(): array
    {
        return T::make(
            'internship-application',
            'Internship Application',
            'Student internship with education and availability.',
            'application-forms',
            [
                T::page('Profile', [
                    T::field('applicant_name', 'text', 'Name', ['required' => true, 'column_span' => 6]),
                    T::field('email', 'email', 'Email', ['required' => true, 'column_span' => 6]),
                    T::field('school', 'text', 'School / university', ['required' => true, 'column_span' => 6]),
                    T::field('major', 'text', 'Major', ['required' => true, 'column_span' => 6]),
                    T::field('graduation_date', 'date', 'Expected graduation', ['column_span' => 6]),
                ]),
                T::page('Internship', [
                    T::field('department', 'select', 'Preferred department', ['required' => true, 'column_span' => 12, 'options' => [
                        ['label' => 'Engineering', 'value' => 'eng'],
                        ['label' => 'Design', 'value' => 'design'],
                        ['label' => 'Marketing', 'value' => 'marketing'],
                        ['label' => 'Operations', 'value' => 'ops'],
                    ]]),
                    T::field('start_date', 'date', 'Available from', ['required' => true, 'column_span' => 6]),
                    T::field('hours_per_week', 'number', 'Hours per week', ['required' => true, 'column_span' => 6]),
                    T::field('resume', 'file', 'Resume', ['required' => true, 'column_span' => 12]),
                    T::field('cover_letter', 'textarea', 'Why this internship?', ['required' => true, 'column_span' => 12, 'meta' => ['use_editor' => true]]),
                ]),
            ],
            'tabs',
            'pro',
            'heroicon-o-academic-cap',
            'Application received — our team will review and respond.',
        );
    }

  /**
   * @return array<string, mixed>
   */
    protected static function petAdoptionApplication(): array
    {
        return T::make(
            'pet-adoption-application',
            'Pet Adoption Application',
            'Adopter screening with lifestyle and pet experience.',
            'application-forms',
            [
                T::page('Adopter', [
                    T::field('applicant_name', 'text', 'Name', ['required' => true, 'column_span' => 6]),
                    T::field('email', 'email', 'Email', ['required' => true, 'column_span' => 6]),
                    T::field('phone', 'phone', 'Phone', ['required' => true, 'column_span' => 6]),
                    T::field('pet_interest', 'select', 'Pet interested in', ['required' => true, 'column_span' => 6, 'options' => [
                        ['label' => 'Dog', 'value' => 'dog'],
                        ['label' => 'Cat', 'value' => 'cat'],
                        ['label' => 'Small animal', 'value' => 'small'],
                    ]]),
                    T::field('household_members', 'number', 'People in household', ['required' => true, 'column_span' => 6]),
                    T::field('children_ages', 'text', 'Children ages', ['column_span' => 6, 'placeholder' => 'e.g. 5, 8']),
                    T::field('landlord_approval', 'toggle', 'Landlord allows pets', ['column_span' => 12]),
                    T::field('vet_reference', 'text', 'Veterinarian reference', ['column_span' => 12]),
                ]),
            ],
        );
    }

  /**
   * @return array<string, mixed>
   */
    protected static function staffApplication(): array
    {
        return T::make(
            'staff-application',
            'Staff Application',
            'Internal or external staff role application.',
            'application-forms',
            [
                T::page('Application', [
                    T::field('applicant_name', 'text', 'Name', ['required' => true, 'column_span' => 6]),
                    T::field('email', 'email', 'Email', ['required' => true, 'column_span' => 6]),
                    T::field('position', 'select', 'Position', ['required' => true, 'column_span' => 12, 'options' => [
                        ['label' => 'Front desk', 'value' => 'front'],
                        ['label' => 'Supervisor', 'value' => 'supervisor'],
                        ['label' => 'Specialist', 'value' => 'specialist'],
                    ]]),
                    T::field('start_date', 'date', 'Earliest start date', ['required' => true, 'column_span' => 6]),
                    T::field('work_authorization', 'toggle', 'Authorized to work', ['required' => true, 'column_span' => 12]),
                    T::field('resume', 'file', 'Resume / CV', ['required' => true, 'column_span' => 12]),
                    T::field('references', 'textarea', 'References', ['column_span' => 12]),
                ]),
            ],
        );
    }

  /**
   * @return array<string, mixed>
   */
    protected static function sponsorshipApplication(): array
    {
        return T::make(
            'sponsorship-application',
            'Sponsorship Application',
            'Event or program sponsorship proposal.',
            'application-forms',
            [
                T::page('Organization', [
                    T::field('org_name', 'text', 'Organization name', ['required' => true, 'column_span' => 6]),
                    T::field('contact_name', 'text', 'Contact name', ['required' => true, 'column_span' => 6]),
                    T::field('email', 'email', 'Email', ['required' => true, 'column_span' => 6]),
                    T::field('website', 'url', 'Website', ['column_span' => 6]),
                    T::field('sponsorship_tier', 'radio', 'Requested tier', ['required' => true, 'column_span' => 12, 'options' => [
                        ['label' => 'Bronze — $1,000', 'value' => 'bronze'],
                        ['label' => 'Silver — $2,500', 'value' => 'silver'],
                        ['label' => 'Gold — $5,000', 'value' => 'gold'],
                    ]]),
                    T::field('proposal', 'textarea', 'Sponsorship proposal', ['required' => true, 'column_span' => 12, 'meta' => ['use_editor' => true]]),
                    T::field('logo', 'file', 'Logo (high resolution)', ['column_span' => 12]),
                ]),
            ],
        );
    }

  /**
   * @return array<string, mixed>
   */
    protected static function tenantApplication(): array
    {
        return T::make(
            'tenant-application',
            'Tenant Application',
            'Rental applicant screening with employment and references.',
            'application-forms',
            [
                T::page('Applicant', [
                    T::field('applicant_name', 'text', 'Full name', ['required' => true, 'column_span' => 6]),
                    T::field('email', 'email', 'Email', ['required' => true, 'column_span' => 6]),
                    T::field('phone', 'phone', 'Phone', ['required' => true, 'column_span' => 6]),
                    T::field('desired_move_in', 'date', 'Desired move-in date', ['required' => true, 'column_span' => 6]),
                    T::field('unit_interest', 'text', 'Unit / property interest', ['column_span' => 12]),
                    T::field('employer', 'text', 'Current employer', ['required' => true, 'column_span' => 6]),
                    T::field('monthly_income', 'number', 'Monthly income', ['required' => true, 'column_span' => 6]),
                    T::field('current_address', 'textarea', 'Current address', ['required' => true, 'column_span' => 12]),
                    T::field('landlord_reference', 'text', 'Current landlord contact', ['column_span' => 12]),
                    T::field('id_document', 'file', 'Photo ID', ['required' => true, 'column_span' => 12]),
                ]),
            ],
        );
    }

  /**
   * @return array<string, mixed>
   */
    protected static function jobApplication(): array
    {
        return T::make(
            'job-application',
            'Job Application',
            'Tabbed application: profile, experience, links.',
            'application-forms',
            [
                T::page('Profile', [
                    T::field('applicant_name', 'text', 'Name', ['required' => true, 'column_span' => 6]),
                    T::field('applicant_email', 'email', 'Email', ['required' => true, 'column_span' => 6]),
                    T::field('role', 'select', 'Role', ['required' => true, 'column_span' => 12, 'options' => [
                        ['label' => 'Product designer', 'value' => 'design'],
                        ['label' => 'Laravel engineer', 'value' => 'eng'],
                        ['label' => 'Customer success', 'value' => 'cs'],
                    ]]),
                ]),
                T::page('Experience', [
                    T::field('years', 'number', 'Years of experience', ['required' => true, 'column_span' => 6]),
                    T::field('skills', 'tags', 'Skills', ['column_span' => 12, 'options' => [
                        ['label' => 'Laravel', 'value' => 'laravel'],
                        ['label' => 'Livewire', 'value' => 'livewire'],
                        ['label' => 'Filament', 'value' => 'filament'],
                    ]]),
                    T::field('summary', 'textarea', 'Summary', ['required' => true, 'column_span' => 12, 'meta' => ['use_editor' => true]]),
                ]),
                T::page('Links', [
                    T::field('portfolio', 'url', 'Portfolio / GitHub', ['column_span' => 12]),
                    T::field('available', 'date', 'Available from', ['column_span' => 6]),
                    T::field('resume', 'file', 'Resume', ['column_span' => 12]),
                ]),
            ],
            'tabs',
            'pro',
            'heroicon-o-briefcase',
            'Application received. We will be in touch if there is a fit.',
        );
    }

  /**
   * @return array<string, mixed>
   */
    protected static function membershipApplication(): array
    {
        return T::make(
            'membership-application',
            'Membership Application',
            'Club or association membership enrollment.',
            'application-forms',
            [
                T::page('Member', [
                    T::field('member_name', 'text', 'Full name', ['required' => true, 'column_span' => 6]),
                    T::field('email', 'email', 'Email', ['required' => true, 'column_span' => 6]),
                    T::field('phone', 'phone', 'Phone', ['column_span' => 6]),
                    T::field('membership_type', 'select', 'Membership type', ['required' => true, 'column_span' => 6, 'options' => [
                        ['label' => 'Individual', 'value' => 'individual'],
                        ['label' => 'Family', 'value' => 'family'],
                        ['label' => 'Corporate', 'value' => 'corporate'],
                    ]]),
                    T::field('referral', 'text', 'Referred by', ['column_span' => 12]),
                    T::field('interests', 'multi_select', 'Areas of interest', ['column_span' => 12, 'options' => [
                        ['label' => 'Events', 'value' => 'events'],
                        ['label' => 'Volunteering', 'value' => 'volunteer'],
                        ['label' => 'Networking', 'value' => 'network'],
                        ['label' => 'Education', 'value' => 'education'],
                    ]]),
                    T::field('agree_terms', 'toggle', 'I agree to membership terms', ['required' => true, 'column_span' => 12]),
                ]),
            ],
        );
    }

  /**
   * @return array<string, mixed>
   */
    protected static function patientIntake(): array
    {
        return T::make(
            'patient-intake',
            'Patient Intake',
            'Medical intake with history, medications, and insurance.',
            'application-forms',
            [
                T::page('Demographics', [
                    T::field('patient_name', 'text', 'Patient name', ['required' => true, 'column_span' => 6]),
                    T::field('date_of_birth', 'date', 'Date of birth', ['required' => true, 'column_span' => 6]),
                    T::field('email', 'email', 'Email', ['column_span' => 6]),
                    T::field('phone', 'phone', 'Phone', ['required' => true, 'column_span' => 6]),
                    T::field('emergency_contact', 'text', 'Emergency contact', ['required' => true, 'column_span' => 6]),
                    T::field('emergency_phone', 'phone', 'Emergency phone', ['required' => true, 'column_span' => 6]),
                ]),
                T::page('Clinical', [
                    T::field('chief_complaint', 'textarea', 'Reason for visit', ['required' => true, 'column_span' => 12]),
                    T::field('allergies', 'tags', 'Allergies', ['column_span' => 12]),
                    T::field('medications', 'textarea', 'Current medications', ['column_span' => 12]),
                    T::field('insurance_provider', 'text', 'Insurance provider', ['column_span' => 6]),
                    T::field('policy_number', 'text', 'Policy number', ['column_span' => 6]),
                ]),
                T::page('Consent', [
                    T::field('privacy_ack', 'toggle', 'I acknowledge the privacy notice', ['required' => true, 'column_span' => 12]),
                    T::field('signature_consent', 'toggle', 'Consent to treatment', ['required' => true, 'column_span' => 12]),
                ]),
            ],
            'accordion',
            'pro',
            'heroicon-o-heart',
            'Intake received — please arrive 15 minutes early.',
        );
    }
}
