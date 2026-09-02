<?php

namespace Spiggle\FormBuilder\Support\Templates;

use Spiggle\FormBuilder\Support\TemplateBuilder as T;
use Spiggle\FormBuilder\Support\TemplateChrome;

class RequestFormTemplates
{
    /**
     * @return list<array<string, mixed>>
     */
    public static function all(): array
    {
        return [
            self::workRequest(),
            self::supplyRequest(),
            self::adjustmentRequest(),
            self::leaveRequest(),
            self::expenseReimbursement(),
            self::contactForm(),
        ];
    }

  /**
   * @return array<string, mixed>
   */
    protected static function workRequest(): array
    {
        return T::make(
            'work-request',
            'Maintenance / Work Request',
            'Report a facility issue or request maintenance.',
            'request-forms',
            [
                T::page('Request', [
                    T::content('heading', ['text' => 'Work request']),
                    T::field('requester_name', 'text', 'Your name', ['required' => true, 'column_span' => 6]),
                    T::field('department', 'text', 'Department / location', ['required' => true, 'column_span' => 6]),
                    T::field('email', 'email', 'Email', ['required' => true, 'column_span' => 6]),
                    T::field('phone', 'phone', 'Phone', ['column_span' => 6]),
                    T::field('category', 'select', 'Category', ['required' => true, 'column_span' => 6, 'options' => [
                        ['label' => 'Electrical', 'value' => 'electrical'],
                        ['label' => 'Plumbing', 'value' => 'plumbing'],
                        ['label' => 'HVAC', 'value' => 'hvac'],
                        ['label' => 'General repair', 'value' => 'general'],
                    ]]),
                    T::field('priority', 'radio', 'Priority', ['required' => true, 'column_span' => 6, 'options' => [
                        ['label' => 'Low', 'value' => 'low'],
                        ['label' => 'Medium', 'value' => 'medium'],
                        ['label' => 'Urgent', 'value' => 'urgent'],
                    ]]),
                    T::field('location_detail', 'text', 'Room / asset location', ['required' => true, 'column_span' => 12]),
                    T::field('description', 'textarea', 'Describe the issue', ['required' => true, 'column_span' => 12]),
                    T::field('photo', 'file', 'Photo of issue', ['column_span' => 12]),
                ]),
            ],
        );
    }

  /**
   * @return array<string, mixed>
   */
    protected static function supplyRequest(): array
    {
        return T::make(
            'supply-request',
            'Supply Request',
            'Request materials or consumables from inventory.',
            'request-forms',
            [
                T::page('Request', [
                    T::field('requester_name', 'text', 'Requester', ['required' => true, 'column_span' => 6]),
                    T::field('needed_by', 'date', 'Needed by', ['required' => true, 'column_span' => 6]),
                    T::field('items', 'textarea', 'Items needed', ['required' => true, 'column_span' => 12, 'placeholder' => 'Item, quantity, catalog #']),
                    T::field('delivery_location', 'text', 'Deliver to', ['required' => true, 'column_span' => 12]),
                    T::field('justification', 'textarea', 'Justification', ['column_span' => 12]),
                ]),
            ],
        );
    }

  /**
   * @return array<string, mixed>
   */
    protected static function adjustmentRequest(): array
    {
        return T::make(
            'adjustment-request',
            'Adjustment Request',
            'Billing or account adjustment with supporting details.',
            'request-forms',
            [
                T::page('Adjustment', [
                    T::field('account_number', 'text', 'Account / invoice #', ['required' => true, 'column_span' => 6]),
                    T::field('requester_name', 'text', 'Requester', ['required' => true, 'column_span' => 6]),
                    T::field('email', 'email', 'Email', ['required' => true, 'column_span' => 6]),
                    T::field('adjustment_type', 'select', 'Adjustment type', ['required' => true, 'column_span' => 6, 'options' => [
                        ['label' => 'Credit', 'value' => 'credit'],
                        ['label' => 'Refund', 'value' => 'refund'],
                        ['label' => 'Fee waiver', 'value' => 'waiver'],
                        ['label' => 'Correction', 'value' => 'correction'],
                    ]]),
                    T::field('amount', 'number', 'Amount', ['required' => true, 'column_span' => 6]),
                    T::field('reason', 'textarea', 'Reason for adjustment', ['required' => true, 'column_span' => 12]),
                    T::field('supporting_docs', 'file', 'Supporting documents', ['column_span' => 12]),
                ]),
            ],
        );
    }

  /**
   * @return array<string, mixed>
   */
    protected static function leaveRequest(): array
    {
        return T::make(
            'leave-request',
            'Employee Leave Request',
            'Request time off with dates and coverage plan.',
            'request-forms',
            [
                T::page('Leave', [
                    T::field('employee_name', 'text', 'Employee name', ['required' => true, 'column_span' => 6]),
                    T::field('employee_id', 'text', 'Employee ID', ['column_span' => 6]),
                    T::field('manager_email', 'email', 'Manager email', ['required' => true, 'column_span' => 6]),
                    T::field('leave_type', 'select', 'Leave type', ['required' => true, 'column_span' => 6, 'options' => [
                        ['label' => 'Vacation', 'value' => 'vacation'],
                        ['label' => 'Sick', 'value' => 'sick'],
                        ['label' => 'Personal', 'value' => 'personal'],
                        ['label' => 'Bereavement', 'value' => 'bereavement'],
                    ]]),
                    T::field('start_date', 'date', 'Start date', ['required' => true, 'column_span' => 6]),
                    T::field('end_date', 'date', 'End date', ['required' => true, 'column_span' => 6]),
                    T::field('coverage_plan', 'textarea', 'Coverage plan', ['column_span' => 12]),
                ]),
            ],
        );
    }

  /**
   * @return array<string, mixed>
   */
    protected static function expenseReimbursement(): array
    {
        return T::make(
            'expense-reimbursement',
            'Expense Reimbursement',
            'Submit business expenses with receipts.',
            'request-forms',
            [
                T::page('Employee', [
                    T::field('employee_name', 'text', 'Employee name', ['required' => true, 'column_span' => 6]),
                    T::field('department', 'text', 'Department', ['required' => true, 'column_span' => 6]),
                ]),
                T::page('Expenses', [
                    T::field('expense_date', 'date', 'Expense date', ['required' => true, 'column_span' => 6]),
                    T::field('category', 'select', 'Category', ['required' => true, 'column_span' => 6, 'options' => [
                        ['label' => 'Travel', 'value' => 'travel'],
                        ['label' => 'Meals', 'value' => 'meals'],
                        ['label' => 'Supplies', 'value' => 'supplies'],
                        ['label' => 'Other', 'value' => 'other'],
                    ]]),
                    T::field('amount', 'number', 'Amount', ['required' => true, 'column_span' => 6]),
                    T::field('currency', 'select', 'Currency', ['column_span' => 6, 'options' => [
                        ['label' => 'USD', 'value' => 'usd'], ['label' => 'EUR', 'value' => 'eur'], ['label' => 'GBP', 'value' => 'gbp'],
                    ]]),
                    T::field('description', 'textarea', 'Business purpose', ['required' => true, 'column_span' => 12]),
                    T::field('receipt', 'file', 'Receipt', ['required' => true, 'column_span' => 12]),
                ]),
            ],
            'pages',
            'pro',
            'heroicon-o-banknotes',
            'Expense submitted for approval.',
        );
    }

  /**
   * @return array<string, mixed>
   */
    protected static function contactForm(): array
    {
        return T::make(
            'contact-us',
            'Contact Form',
            'A simple contact form for website visitors.',
            'request-forms',
            [
                T::page('Message', [
                    T::field('full_name', 'text', 'Full name', ['required' => true, 'column_span' => 6, 'placeholder' => 'Ada Lovelace']),
                    T::field('email', 'email', 'Email', ['required' => true, 'column_span' => 6, 'validation_rules' => ['email']]),
                    T::field('phone', 'phone', 'Phone', ['column_span' => 6]),
                    T::field('topic', 'select', 'Topic', ['required' => true, 'column_span' => 6, 'options' => [
                        ['label' => 'General', 'value' => 'general'],
                        ['label' => 'Sales', 'value' => 'sales'],
                        ['label' => 'Support', 'value' => 'support'],
                    ]]),
                    T::field('message', 'textarea', 'How can we help?', ['required' => true, 'column_span' => 12, 'meta' => ['rows' => 5]]),
                    T::field('marketing_opt_in', 'toggle', 'Send me product updates', ['column_span' => 12]),
                ]),
            ],
            'single',
            'core',
            'heroicon-o-envelope',
            'Thanks for writing in — we typically reply within one business day.',
            TemplateChrome::contactShowcase(),
        );
    }
}
