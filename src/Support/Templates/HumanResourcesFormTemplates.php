<?php

namespace Spiggle\FormBuilder\Support\Templates;

use Spiggle\FormBuilder\Support\TemplateBuilder as T;

class HumanResourcesFormTemplates
{
    /**
     * @return list<array<string, mixed>>
     */
    public static function all(): array
    {
        return [
            self::employeeInformation(),
            self::leaveOfAbsence(),
            self::timeTracking(),
            self::incidentReport(),
            self::exitInterview(),
            self::performanceReview(),
        ];
    }

  /**
   * @return array<string, mixed>
   */
    protected static function employeeInformation(): array
    {
        return T::make(
            'employee-information',
            'Employee Information',
            'New hire or update employee personal and contact details.',
            'human-resources',
            [
                T::page('Employee', [
                    T::field('employee_name', 'text', 'Legal name', ['required' => true, 'column_span' => 6]),
                    T::field('preferred_name', 'text', 'Preferred name', ['column_span' => 6]),
                    T::field('employee_id', 'text', 'Employee ID', ['column_span' => 6]),
                    T::field('start_date', 'date', 'Start date', ['required' => true, 'column_span' => 6]),
                    T::field('email', 'email', 'Work email', ['required' => true, 'column_span' => 6]),
                    T::field('phone', 'phone', 'Phone', ['required' => true, 'column_span' => 6]),
                    T::field('department', 'select', 'Department', ['required' => true, 'column_span' => 6, 'options' => [
                        ['label' => 'Engineering', 'value' => 'eng'],
                        ['label' => 'Sales', 'value' => 'sales'],
                        ['label' => 'Operations', 'value' => 'ops'],
                        ['label' => 'HR', 'value' => 'hr'],
                    ]]),
                    T::field('home_address', 'textarea', 'Home address', ['required' => true, 'column_span' => 12]),
                    T::field('emergency_contact', 'text', 'Emergency contact', ['required' => true, 'column_span' => 6]),
                    T::field('emergency_phone', 'phone', 'Emergency phone', ['required' => true, 'column_span' => 6]),
                ]),
            ],
        );
    }

  /**
   * @return array<string, mixed>
   */
    protected static function leaveOfAbsence(): array
    {
        return T::make(
            'leave-of-absence',
            'Leave of Absence',
            'Extended leave request with medical or personal documentation.',
            'human-resources',
            [
                T::page('Leave', [
                    T::field('employee_name', 'text', 'Employee name', ['required' => true, 'column_span' => 6]),
                    T::field('employee_id', 'text', 'Employee ID', ['required' => true, 'column_span' => 6]),
                    T::field('leave_reason', 'select', 'Reason', ['required' => true, 'column_span' => 12, 'options' => [
                        ['label' => 'Medical', 'value' => 'medical'],
                        ['label' => 'Parental', 'value' => 'parental'],
                        ['label' => 'Personal', 'value' => 'personal'],
                        ['label' => 'Military', 'value' => 'military'],
                    ]]),
                    T::field('start_date', 'date', 'Leave start', ['required' => true, 'column_span' => 6]),
                    T::field('expected_return', 'date', 'Expected return', ['column_span' => 6]),
                    T::field('documentation', 'file', 'Supporting documentation', ['column_span' => 12]),
                    T::field('notes', 'textarea', 'Additional notes', ['column_span' => 12]),
                ]),
            ],
        );
    }

  /**
   * @return array<string, mixed>
   */
    protected static function timeTracking(): array
    {
        return T::make(
            'time-tracking',
            'Time Tracking',
            'Log hours worked with project and task codes.',
            'human-resources',
            [
                T::page('Timesheet', [
                    T::field('employee_name', 'text', 'Employee', ['required' => true, 'column_span' => 6]),
                    T::field('work_date', 'date', 'Date', ['required' => true, 'column_span' => 6]),
                    T::field('project_code', 'text', 'Project code', ['required' => true, 'column_span' => 6]),
                    T::field('task_description', 'text', 'Task', ['required' => true, 'column_span' => 6]),
                    T::field('hours', 'number', 'Hours worked', ['required' => true, 'column_span' => 6, 'validation_rules' => ['min:0.25', 'max:24']]),
                    T::field('overtime', 'toggle', 'Overtime hours', ['column_span' => 6]),
                    T::field('notes', 'textarea', 'Notes', ['column_span' => 12]),
                ]),
            ],
        );
    }

  /**
   * @return array<string, mixed>
   */
    protected static function incidentReport(): array
    {
        return T::make(
            'incident-report',
            'Incident Report',
            'Workplace incident documentation for HR review.',
            'human-resources',
            [
                T::page('Incident', [
                    T::field('reporter_name', 'text', 'Reporter', ['required' => true, 'column_span' => 6]),
                    T::field('incident_date', 'datetime', 'Date & time of incident', ['required' => true, 'column_span' => 6]),
                    T::field('location', 'text', 'Location', ['required' => true, 'column_span' => 12]),
                    T::field('incident_type', 'select', 'Incident type', ['required' => true, 'column_span' => 6, 'options' => [
                        ['label' => 'Injury', 'value' => 'injury'],
                        ['label' => 'Near miss', 'value' => 'near_miss'],
                        ['label' => 'Harassment', 'value' => 'harassment'],
                        ['label' => 'Property damage', 'value' => 'damage'],
                        ['label' => 'Other', 'value' => 'other'],
                    ]]),
                    T::field('people_involved', 'textarea', 'People involved', ['required' => true, 'column_span' => 12]),
                    T::field('description', 'textarea', 'Description of incident', ['required' => true, 'column_span' => 12]),
                    T::field('witnesses', 'textarea', 'Witnesses', ['column_span' => 12]),
                    T::field('attachments', 'file', 'Photos or documents', ['column_span' => 12]),
                ]),
            ],
        );
    }

  /**
   * @return array<string, mixed>
   */
    protected static function exitInterview(): array
    {
        return T::make(
            'exit-interview',
            'Exit Interview',
            'Departing employee feedback and offboarding details.',
            'human-resources',
            [
                T::page('Exit', [
                    T::field('employee_name', 'text', 'Employee name', ['required' => true, 'column_span' => 6]),
                    T::field('last_day', 'date', 'Last day', ['required' => true, 'column_span' => 6]),
                    T::field('reason_leaving', 'select', 'Primary reason for leaving', ['required' => true, 'column_span' => 12, 'options' => [
                        ['label' => 'New opportunity', 'value' => 'new_job'],
                        ['label' => 'Relocation', 'value' => 'relocation'],
                        ['label' => 'Compensation', 'value' => 'comp'],
                        ['label' => 'Work-life balance', 'value' => 'balance'],
                        ['label' => 'Other', 'value' => 'other'],
                    ]]),
                    T::field('enjoyed_most', 'textarea', 'What did you enjoy most?', ['column_span' => 12]),
                    T::field('improve', 'textarea', 'What could we improve?', ['column_span' => 12]),
                    T::field('recommend', 'radio', 'Would you recommend us as an employer?', ['column_span' => 12, 'options' => [
                        ['label' => 'Yes', 'value' => 'yes'], ['label' => 'Maybe', 'value' => 'maybe'], ['label' => 'No', 'value' => 'no'],
                    ]]),
                ]),
            ],
        );
    }

  /**
   * @return array<string, mixed>
   */
    protected static function performanceReview(): array
    {
        return T::make(
            'performance-review',
            'Performance Review',
            'Manager review with goals, competencies, and feedback.',
            'human-resources',
            [
                T::page('Review period', [
                    T::field('employee_name', 'text', 'Employee', ['required' => true, 'column_span' => 6]),
                    T::field('reviewer_name', 'text', 'Reviewer', ['required' => true, 'column_span' => 6]),
                    T::field('review_period', 'text', 'Review period', ['required' => true, 'column_span' => 6, 'placeholder' => 'Q1 2026']),
                ]),
                T::page('Ratings', [
                    T::field('quality_rating', 'select', 'Quality of work', ['required' => true, 'column_span' => 6, 'options' => collect(range(1, 5))->map(fn ($n) => ['label' => (string) $n, 'value' => (string) $n])->all()]),
                    T::field('collaboration_rating', 'select', 'Collaboration', ['required' => true, 'column_span' => 6, 'options' => collect(range(1, 5))->map(fn ($n) => ['label' => (string) $n, 'value' => (string) $n])->all()]),
                    T::field('strengths', 'textarea', 'Strengths', ['required' => true, 'column_span' => 12]),
                    T::field('development_areas', 'textarea', 'Development areas', ['column_span' => 12]),
                    T::field('goals', 'textarea', 'Goals for next period', ['column_span' => 12]),
                ]),
            ],
            'accordion',
            'pro',
            'heroicon-o-chart-bar',
            'Performance review saved.',
        );
    }
}
