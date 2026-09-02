<?php

namespace Spiggle\FormBuilder\Support\Templates;

use Spiggle\FormBuilder\Support\TemplateBuilder as T;
use Spiggle\FormBuilder\Support\TemplateChrome;

class PaymentFormTemplates
{
    /**
     * @return list<array<string, mixed>>
     */
    public static function all(): array
    {
        return [
            self::donation(),
            self::onlinePayment(),
            self::invoicePayment(),
        ];
    }

  /**
   * @return array<string, mixed>
   */
    protected static function donation(): array
    {
        return T::make(
            'donation',
            'Donation',
            'Collect charitable donations with amount and dedication.',
            'payment-forms',
            [
                T::page('Donation', [
                    T::content('heading', ['text' => 'Make a donation']),
                    T::field('donor_name', 'text', 'Full name', ['required' => true, 'column_span' => 6]),
                    T::field('email', 'email', 'Email', ['required' => true, 'column_span' => 6]),
                    T::field('amount', 'radio', 'Donation amount', ['required' => true, 'column_span' => 12, 'options' => [
                        ['label' => '$25', 'value' => '25'],
                        ['label' => '$50', 'value' => '50'],
                        ['label' => '$100', 'value' => '100'],
                        ['label' => 'Other', 'value' => 'other'],
                    ]]),
                    T::field('custom_amount', 'number', 'Custom amount', ['column_span' => 6]),
                    T::field('fund', 'select', 'Designate to fund', ['column_span' => 6, 'options' => [
                        ['label' => 'General fund', 'value' => 'general'],
                        ['label' => 'Scholarship', 'value' => 'scholarship'],
                        ['label' => 'Emergency relief', 'value' => 'relief'],
                    ]]),
                    T::field('dedication', 'text', 'In honor / memory of', ['column_span' => 12]),
                    T::content('paragraph', ['text' => 'Payment will be processed securely via Stripe or PayPal after you submit this form.']),
                    T::field('payment_method', 'select', 'Payment method', ['required' => true, 'column_span' => 12, 'options' => [
                        ['label' => 'Credit card (Stripe)', 'value' => 'stripe'],
                        ['label' => 'PayPal', 'value' => 'paypal'],
                    ]]),
                    T::field('anonymous', 'toggle', 'Make my donation anonymous', ['column_span' => 12]),
                ]),
            ],
            'single',
            'core',
            'heroicon-o-heart',
            'Thank you for your generous donation.',
            TemplateChrome::donationShowcase(),
        );
    }

  /**
   * @return array<string, mixed>
   */
    protected static function onlinePayment(): array
    {
        return T::make(
            'online-payment',
            'Online Payment',
            'Product or service payment with gateway placeholders.',
            'payment-forms',
            [
                T::page('Payment', [
                    T::field('payer_name', 'text', 'Name on card / account', ['required' => true, 'column_span' => 6]),
                    T::field('email', 'email', 'Receipt email', ['required' => true, 'column_span' => 6]),
                    T::field('invoice_number', 'text', 'Invoice or order #', ['column_span' => 6]),
                    T::field('amount', 'number', 'Amount due', ['required' => true, 'column_span' => 6]),
                    T::field('currency', 'select', 'Currency', ['column_span' => 6, 'options' => [
                        ['label' => 'USD', 'value' => 'usd'], ['label' => 'EUR', 'value' => 'eur'], ['label' => 'GBP', 'value' => 'gbp'],
                    ]]),
                    T::content('divider', []),
                    T::content('paragraph', ['text' => 'Select a payment provider. Card details are collected on the secure checkout page (Stripe Elements / PayPal Smart Buttons).']),
                    T::field('gateway', 'radio', 'Payment gateway', ['required' => true, 'column_span' => 12, 'options' => [
                        ['label' => 'Stripe', 'value' => 'stripe'],
                        ['label' => 'PayPal', 'value' => 'paypal'],
                        ['label' => 'Square', 'value' => 'square'],
                    ]]),
                    T::field('billing_address', 'textarea', 'Billing address', ['required' => true, 'column_span' => 12, 'meta' => ['rows' => 3]]),
                ]),
            ],
        );
    }

  /**
   * @return array<string, mixed>
   */
    protected static function invoicePayment(): array
    {
        return T::make(
            'invoice-payment',
            'Invoice Payment',
            'Pay an outstanding invoice with reference number.',
            'payment-forms',
            [
                T::page('Invoice', [
                    T::field('company_name', 'text', 'Company name', ['column_span' => 6]),
                    T::field('contact_name', 'text', 'Contact name', ['required' => true, 'column_span' => 6]),
                    T::field('email', 'email', 'Email', ['required' => true, 'column_span' => 6]),
                    T::field('invoice_number', 'text', 'Invoice number', ['required' => true, 'column_span' => 6]),
                    T::field('invoice_amount', 'number', 'Invoice amount', ['required' => true, 'column_span' => 6]),
                    T::field('payment_amount', 'number', 'Payment amount', ['required' => true, 'column_span' => 6]),
                    T::content('paragraph', ['text' => 'You will be redirected to Stripe Checkout or PayPal to complete payment securely.']),
                    T::field('payment_provider', 'select', 'Payment provider', ['required' => true, 'column_span' => 12, 'options' => [
                        ['label' => 'Stripe Checkout', 'value' => 'stripe_checkout'],
                        ['label' => 'PayPal', 'value' => 'paypal'],
                    ]]),
                    T::field('notes', 'textarea', 'Payment notes', ['column_span' => 12]),
                ]),
            ],
        );
    }
}
