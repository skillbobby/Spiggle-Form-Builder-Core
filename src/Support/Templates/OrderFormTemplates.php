<?php

namespace Spiggle\FormBuilder\Support\Templates;

use Spiggle\FormBuilder\Support\TemplateBuilder as T;
use Spiggle\FormBuilder\Support\TemplateChrome;

class OrderFormTemplates
{
    /**
     * @return list<array<string, mixed>>
     */
    public static function all(): array
    {
        return [
            self::productOrder(),
            self::foodBeverageOrder(),
            self::deliveryOrder(),
            self::supplyOrder(),
            self::apparelOrder(),
        ];
    }

  /**
   * @return array<string, mixed>
   */
    protected static function productOrder(): array
    {
        return T::make(
            'product-order',
            'Product Order',
            'Catalog order with shipping and billing details.',
            'order-forms',
            [
                T::page('Order details', [
                    T::content('heading', ['text' => 'Product order', 'level' => 2]),
                    T::content('paragraph', ['text' => 'Tell us what you need and where to ship it.']),
                    T::field('customer_name', 'text', 'Full name', ['required' => true, 'column_span' => 6, 'placeholder' => 'Jane Cooper']),
                    T::field('customer_email', 'email', 'Email', ['required' => true, 'column_span' => 6]),
                    T::field('customer_phone', 'phone', 'Phone', ['column_span' => 6]),
                    T::field('company', 'text', 'Company', ['column_span' => 6, 'placeholder' => 'Optional']),
                    T::field('product', 'select', 'Product', ['required' => true, 'column_span' => 6, 'options' => [
                        ['label' => 'Starter kit', 'value' => 'starter'],
                        ['label' => 'Professional bundle', 'value' => 'pro'],
                        ['label' => 'Enterprise license', 'value' => 'enterprise'],
                    ]]),
                    T::field('quantity', 'number', 'Quantity', ['required' => true, 'column_span' => 6, 'validation_rules' => ['min:1']]),
                    T::field('sku_notes', 'textarea', 'SKU / variant notes', ['column_span' => 12, 'placeholder' => 'Color, size, or configuration']),
                ]),
                T::page('Shipping', [
                    T::field('ship_address', 'textarea', 'Shipping address', ['required' => true, 'column_span' => 12, 'meta' => ['rows' => 3]]),
                    T::field('ship_method', 'radio', 'Shipping method', ['required' => true, 'column_span' => 12, 'options' => [
                        ['label' => 'Standard (5–7 days)', 'value' => 'standard'],
                        ['label' => 'Express (2–3 days)', 'value' => 'express'],
                        ['label' => 'Pickup', 'value' => 'pickup'],
                    ]]),
                    T::field('po_number', 'text', 'PO number', ['column_span' => 6]),
                ]),
            ],
            'pages',
            'pro',
            'heroicon-o-shopping-bag',
            'Order received — we will confirm availability and send an invoice.',
            TemplateChrome::orderShowcase(),
        );
    }

  /**
   * @return array<string, mixed>
   */
    protected static function foodBeverageOrder(): array
    {
        return T::make(
            'food-beverage-order',
            'Food & Beverage Order',
            'Catering or restaurant order with dietary preferences.',
            'order-forms',
            [
                T::page('Order', [
                    T::content('heading', ['text' => 'Food & beverage order']),
                    T::field('event_name', 'text', 'Event or table name', ['required' => true, 'column_span' => 6]),
                    T::field('service_date', 'date', 'Service date', ['required' => true, 'column_span' => 6]),
                    T::field('service_time', 'text', 'Service time', ['column_span' => 6, 'placeholder' => '12:30 PM']),
                    T::field('guest_count', 'number', 'Guest count', ['required' => true, 'column_span' => 6]),
                    T::field('menu_package', 'select', 'Menu package', ['required' => true, 'column_span' => 12, 'options' => [
                        ['label' => 'Breakfast buffet', 'value' => 'breakfast'],
                        ['label' => 'Lunch boxed meals', 'value' => 'lunch'],
                        ['label' => 'Dinner plated', 'value' => 'dinner'],
                        ['label' => 'Cocktail reception', 'value' => 'cocktail'],
                    ]]),
                    T::field('items', 'textarea', 'Menu items & quantities', ['required' => true, 'column_span' => 12, 'placeholder' => 'e.g. 24 chicken wraps, 12 vegetarian']),
                    T::field('dietary', 'multi_select', 'Dietary requirements', ['column_span' => 12, 'options' => [
                        ['label' => 'Vegetarian', 'value' => 'veg'],
                        ['label' => 'Vegan', 'value' => 'vegan'],
                        ['label' => 'Gluten-free', 'value' => 'gf'],
                        ['label' => 'Nut allergy', 'value' => 'nuts'],
                        ['label' => 'Halal', 'value' => 'halal'],
                    ]]),
                    T::field('contact_name', 'text', 'Contact name', ['required' => true, 'column_span' => 6]),
                    T::field('contact_phone', 'phone', 'Contact phone', ['required' => true, 'column_span' => 6]),
                ]),
            ],
            'single',
            'core',
            'heroicon-o-cake',
            'Thanks — our kitchen team will confirm your order shortly.',
        );
    }

  /**
   * @return array<string, mixed>
   */
    protected static function deliveryOrder(): array
    {
        return T::make(
            'delivery-order',
            'Delivery Order',
            'Schedule a delivery with drop-off instructions.',
            'order-forms',
            [
                T::page('Delivery', [
                    T::field('sender_name', 'text', 'Sender name', ['required' => true, 'column_span' => 6]),
                    T::field('sender_phone', 'phone', 'Sender phone', ['required' => true, 'column_span' => 6]),
                    T::field('recipient_name', 'text', 'Recipient name', ['required' => true, 'column_span' => 6]),
                    T::field('recipient_phone', 'phone', 'Recipient phone', ['column_span' => 6]),
                    T::field('pickup_address', 'textarea', 'Pickup address', ['required' => true, 'column_span' => 12, 'meta' => ['rows' => 2]]),
                    T::field('delivery_address', 'textarea', 'Delivery address', ['required' => true, 'column_span' => 12, 'meta' => ['rows' => 2]]),
                    T::field('delivery_date', 'date', 'Preferred delivery date', ['required' => true, 'column_span' => 6]),
                    T::field('time_window', 'select', 'Time window', ['required' => true, 'column_span' => 6, 'options' => [
                        ['label' => 'Morning (8 AM – 12 PM)', 'value' => 'morning'],
                        ['label' => 'Afternoon (12 – 5 PM)', 'value' => 'afternoon'],
                        ['label' => 'Evening (5 – 9 PM)', 'value' => 'evening'],
                    ]]),
                    T::field('package_description', 'textarea', 'Package description', ['required' => true, 'column_span' => 12]),
                    T::field('signature_required', 'toggle', 'Signature required on delivery', ['column_span' => 12]),
                    T::field('instructions', 'textarea', 'Special instructions', ['column_span' => 12, 'placeholder' => 'Gate code, loading dock, etc.']),
                ]),
            ],
        );
    }

  /**
   * @return array<string, mixed>
   */
    protected static function supplyOrder(): array
    {
        return T::make(
            'supply-order',
            'Supply Order',
            'Office or warehouse supply requisition.',
            'order-forms',
            [
                T::page('Requisition', [
                    T::field('requester_name', 'text', 'Requester', ['required' => true, 'column_span' => 6]),
                    T::field('department', 'select', 'Department', ['required' => true, 'column_span' => 6, 'options' => [
                        ['label' => 'Operations', 'value' => 'ops'],
                        ['label' => 'Facilities', 'value' => 'facilities'],
                        ['label' => 'IT', 'value' => 'it'],
                        ['label' => 'HR', 'value' => 'hr'],
                    ]]),
                    T::field('cost_center', 'text', 'Cost center', ['column_span' => 6]),
                    T::field('needed_by', 'date', 'Needed by', ['required' => true, 'column_span' => 6]),
                    T::field('items', 'textarea', 'Items requested', ['required' => true, 'column_span' => 12, 'placeholder' => 'Item, quantity, part number']),
                    T::field('priority', 'radio', 'Priority', ['required' => true, 'column_span' => 12, 'options' => [
                        ['label' => 'Routine', 'value' => 'routine'],
                        ['label' => 'Urgent', 'value' => 'urgent'],
                    ]]),
                    T::field('justification', 'textarea', 'Business justification', ['column_span' => 12]),
                ]),
            ],
        );
    }

  /**
   * @return array<string, mixed>
   */
    protected static function apparelOrder(): array
    {
        return T::make(
            'apparel-order',
            'Apparel Order',
            'Team uniforms or branded merchandise sizing.',
            'order-forms',
            [
                T::page('Sizing', [
                    T::content('heading', ['text' => 'Apparel order']),
                    T::field('team_name', 'text', 'Team or group name', ['required' => true, 'column_span' => 12]),
                    T::field('wearer_name', 'text', 'Wearer name', ['required' => true, 'column_span' => 6]),
                    T::field('wearer_email', 'email', 'Email', ['required' => true, 'column_span' => 6]),
                    T::field('garment', 'select', 'Garment', ['required' => true, 'column_span' => 6, 'options' => [
                        ['label' => 'T-shirt', 'value' => 'tee'],
                        ['label' => 'Polo', 'value' => 'polo'],
                        ['label' => 'Hoodie', 'value' => 'hoodie'],
                        ['label' => 'Jacket', 'value' => 'jacket'],
                    ]]),
                    T::field('size', 'select', 'Size', ['required' => true, 'column_span' => 6, 'options' => [
                        ['label' => 'XS', 'value' => 'xs'], ['label' => 'S', 'value' => 's'], ['label' => 'M', 'value' => 'm'],
                        ['label' => 'L', 'value' => 'l'], ['label' => 'XL', 'value' => 'xl'], ['label' => '2XL', 'value' => '2xl'],
                    ]]),
                    T::field('color', 'select', 'Color', ['column_span' => 6, 'options' => [
                        ['label' => 'Navy', 'value' => 'navy'], ['label' => 'Black', 'value' => 'black'], ['label' => 'Heather gray', 'value' => 'gray'],
                    ]]),
                    T::field('quantity', 'number', 'Quantity', ['required' => true, 'column_span' => 6, 'validation_rules' => ['min:1']]),
                    T::field('name_on_garment', 'text', 'Name on garment', ['column_span' => 6, 'placeholder' => 'Optional embroidery']),
                    T::field('number_on_garment', 'number', 'Number on garment', ['column_span' => 6]),
                ]),
            ],
        );
    }
}
