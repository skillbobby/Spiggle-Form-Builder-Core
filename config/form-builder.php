<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Community upsell (Core)
    |--------------------------------------------------------------------------
    */
    'upsell' => [
        'checkout_url' => env('FORM_BUILDER_CHECKOUT_URL', env('LEMON_SQUEEZY_CHECKOUT_URL', 'https://kodesmart.lemonsqueezy.com/checkout/buy/363bfd12-ed05-42aa-b04b-f9a4a5e2c134')),
        'checkout_allowed_hosts' => array_values(array_filter(array_map(
            'trim',
            explode(',', (string) env('FORM_BUILDER_CHECKOUT_ALLOWED_HOSTS', 'lemonsqueezy.com'))
        ))),
    ],

    /*
    |--------------------------------------------------------------------------
    | Table Names
    |--------------------------------------------------------------------------
    */
    'tables' => [
        'forms' => 'form_builder_forms',
        'submissions' => 'form_builder_submissions',
        'audit_logs' => 'form_builder_audit_logs',
    ],

    /*
    |--------------------------------------------------------------------------
    | Schema
    |--------------------------------------------------------------------------
    */
    'schema_version' => '1.0',

    /*
    |--------------------------------------------------------------------------
    | Public Routing
    |--------------------------------------------------------------------------
    | Forms are served at /{route_prefix}/{base_path}. Set root_paths to true
    | to also intercept unmatched web requests whose path matches a published
    | form (conflict resolution still appends a hash when needed).
    */
    'route_prefix' => env('FORM_BUILDER_ROUTE_PREFIX', 'forms'),
    'root_paths' => (bool) env('FORM_BUILDER_ROOT_PATHS', false),
    'reserved_paths' => ['admin', 'livewire', 'api', 'up', 'storage', 'vendor'],

    /*
    |--------------------------------------------------------------------------
    | Filament Navigation
    |--------------------------------------------------------------------------
    */
    'navigation' => [
        'group' => 'Forms',
        'forms_icon' => 'heroicon-o-clipboard-document-list',
        'submissions_icon' => 'heroicon-o-inbox-stack',
        'forms_sort' => 40,
        'submissions_sort' => 41,
    ],

    /*
    |--------------------------------------------------------------------------
    | Role-Based Access
    |--------------------------------------------------------------------------
    | Shield / Spatie permission names. If a permission has not been generated
    | yet, authenticated panel users retain access (same pattern as Dynamic Fields).
    */
    'permissions' => [
        'manage_forms' => 'manage_forms',
        'view_submissions' => 'view_form_submissions',
        'export_submissions' => 'export_form_submissions',
        'manage_submissions' => 'manage_form_submissions',
    ],

    /*
    |--------------------------------------------------------------------------
    | Submissions
    |--------------------------------------------------------------------------
    */
    'submissions' => [
        'statuses' => [
            'new' => 'New',
            'reviewed' => 'Reviewed',
            'archived' => 'Archived',
            'spam' => 'Spam',
        ],
        'store_ip' => true,
        'hash_ip' => false,
        'store_user_agent' => true,
        'sanitize_html' => true,
    ],

    /*
    |--------------------------------------------------------------------------
    | Files
    |--------------------------------------------------------------------------
    */
    'files' => [
        'disk' => env('FORM_BUILDER_FILE_DISK', 'public'),
        'directory' => 'form-submissions',
        'max_size_kb' => 5120,
    ],

    /*
    |--------------------------------------------------------------------------
    | Exports
    |--------------------------------------------------------------------------
    */
    'exports' => [
        'disk' => 'local',
        'directory' => 'form-builder-exports',
        'queue' => env('FORM_BUILDER_EXPORT_QUEUE', 'default'),
        'formats' => ['csv', 'xlsx', 'pdf'],
    ],

    /*
    |--------------------------------------------------------------------------
    | Notifications
    |--------------------------------------------------------------------------
    */
    'notify' => [
        'enabled' => true,
        'mail_from' => null,
    ],

    /*
    |--------------------------------------------------------------------------
    | Layout
    |--------------------------------------------------------------------------
    */
    'container_types' => [
        'single' => 'Single page',
        'wizard' => 'Wizard',
        'tabs' => 'Tabs',
        'pages' => 'Pages',
    ],

    'label_positions' => [
        'above' => 'Above',
        'inline' => 'Inline',
        'below' => 'Below',
        'inside' => 'Inside (placeholder / floating)',
    ],

    /*
    |--------------------------------------------------------------------------
    | Field types (used when Dynamic Fields is not installed)
    |--------------------------------------------------------------------------
    */
    'field_types' => [
        'text' => 'Text',
        'textarea' => 'Textarea',
        'select' => 'Select',
        'multi_select' => 'Multi-select',
        'tags' => 'Tags',
        'radio' => 'Radio',
        'date' => 'Date',
        'datetime' => 'Date & Time',
        'boolean' => 'Boolean',
        'toggle' => 'Toggle',
        'number' => 'Number',
        'email' => 'Email',
        'phone' => 'Phone',
        'url' => 'URL',
        'file' => 'File',
    ],

    'drafts' => [
        'enabled' => true,
        'session_key' => 'form_builder_drafts',
    ],

];

