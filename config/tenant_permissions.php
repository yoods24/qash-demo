<?php

return [
    'modules' => [
        [
            'key' => 'operations',
            'label' => 'Operations & Home',
            'view' => 'operations_view',
            'children' => [
                ['key' => 'dashboard', 'label' => 'Dashboard', 'view' => 'dashboard_view', 'actions' => []],
                ['key' => 'attendance', 'label' => 'Attendance', 'view' => 'attendance_view', 'actions' => []],
                [
                    'key' => 'profile',
                    'label' => 'Staff Profile',
                    'view' => 'profile_view',
                    'actions' => [
                        ['name' => 'profile_update', 'label' => 'Update Profile'],
                        ['name' => 'profile_password_update', 'label' => 'Update Password'],
                        ['name' => 'profile_notification_update', 'label' => 'Update Notification Preferences'],
                    ],
                ],
                ['key' => 'notifications', 'label' => 'Notifications', 'view' => 'notification_view', 'actions' => []],
                [
                    'key' => 'taxes',
                    'label' => 'Taxes',
                    'view' => 'taxes_view',
                    'actions' => [
                        ['name' => 'taxes_create', 'label' => 'Create or Update Tax'],
                        ['name' => 'taxes_delete', 'label' => 'Delete Tax'],
                    ],
                ],
                [
                    'key' => 'settings',
                    'label' => 'Settings',
                    'view' => 'settings_view',
                    'actions' => [
                        ['name' => 'settings_general_update', 'label' => 'Update General Information'],
                        ['name' => 'settings_invoice_update', 'label' => 'Update Invoice Settings'],
                        ['name' => 'settings_attendance_update', 'label' => 'Update Attendance Settings'],
                        ['name' => 'settings_geolocation_update', 'label' => 'Update Geolocation Settings'],
                    ],
                ],
                [
                    'key' => 'invoice_templates',
                    'label' => 'Invoice Templates',
                    'view' => 'invoice_templates_view',
                    'actions' => [
                        ['name' => 'invoice_templates_select', 'label' => 'Select Template'],
                    ],
                ],
                [
                    'key' => 'tenant_profile',
                    'label' => 'Tenant Profile',
                    'view' => 'tenant_profile_view',
                    'actions' => [
                        ['name' => 'tenant_profile_update_about', 'label' => 'Update About'],
                        ['name' => 'tenant_profile_update_brand_info', 'label' => 'Update Brand Info'],
                        ['name' => 'tenant_profile_update_general_info', 'label' => 'Update General Info'],
                    ],
                ],
            ],
        ],
        [
            'key' => 'pos',
            'label' => 'POS & Orders',
            'view' => 'pos_view',
            'children' => [
                [
                    'key' => 'pos_orders',
                    'label' => 'POS Orders',
                    'view' => 'pos_orders_view',
                    'actions' => [],
                ],
            ],
        ],
        [
            'key' => 'tables',
            'label' => 'Table Management',
            'view' => 'tables_view',
            'children' => [
                [
                    'key' => 'dining_tables',
                    'label' => 'Dining Tables',
                    'view' => 'pos_table_orders_view',
                    'actions' => [
                        ['name' => 'dining_tables_create', 'label' => 'Create Table'],
                        ['name' => 'dining_tables_update', 'label' => 'Update Table'],
                        ['name' => 'dining_tables_positions_update', 'label' => 'Update Layout Positions'],
                        ['name' => 'dining_tables_delete', 'label' => 'Delete Table'],
                        ['name' => 'dining_tables_qr_generate', 'label' => 'Generate Table QR'],
                        ['name' => 'floors_create', 'label' => 'Create Floor'],
                        ['name' => 'floors_update', 'label' => 'Update Floor'],
                        ['name' => 'floors_delete', 'label' => 'Delete Floor'],
                    ],
                ],
                [
                    'key' => 'table_information',
                    'label' => 'Table Information',
                    'view' => 'table_information_view',
                    'actions' => [],
                ],
                [
                    'key' => 'table_plan',
                    'label' => 'Table Plan',
                    'view' => 'table_plan_view',
                    'actions' => [],
                ],
            ],
        ],
        [
            'key' => 'kitchen',
            'label' => 'Kitchen',
            'view' => 'kitchen_view',
            'children' => [
                [
                    'key' => 'kds',
                    'label' => 'Kitchen Display System',
                    'view' => 'kitchen_kds_view',
                    'actions' => [
                        ['name' => 'kitchen_kds_update_order', 'label' => 'Update Order'],
                        ['name' => 'kitchen_kds_confirm_order', 'label' => 'Confirm Order'],
                        ['name' => 'kitchen_order_mark', 'label' => 'Mark Order Status'],
                    ],
                ],
                [
                    'key' => 'oss',
                    'label' => 'Order Support Station',
                    'view' => 'kitchen_oss_view',
                    'actions' => [
                        ['name' => 'kitchen_oss_update_order', 'label' => 'Update Order'],
                        ['name' => 'kitchen_oss_confirm_order', 'label' => 'Confirm Order'],
                    ],
                ],
            ],
        ],
        [
            'key' => 'inventory',
            'label' => 'Inventory',
            'view' => 'inventory_view',
            'children' => [
                [
                    'key' => 'products',
                    'label' => 'Products',
                    'view' => 'inventory_products_view',
                    'actions' => [
                        ['name' => 'inventory_products_create', 'label' => 'Create Product'],
                        ['name' => 'inventory_products_update', 'label' => 'Update Product'],
                        ['name' => 'inventory_products_delete', 'label' => 'Delete Product'],
                        ['name' => 'inventory_product_options_manage', 'label' => 'Manage Product Options'],
                    ],
                ],
                [
                    'key' => 'category',
                    'label' => 'Category',
                    'view' => 'inventory_category_view',
                    'actions' => [
                        ['name' => 'inventory_category_create', 'label' => 'Create Category'],
                        ['name' => 'inventory_category_delete', 'label' => 'Delete Category'],
                    ],
                ],
                [
                    'key' => 'sub_category',
                    'label' => 'Sub Category',
                    'view' => 'inventory_sub_category_view',
                    'actions' => [],
                ],
                [
                    'key' => 'special_type',
                    'label' => 'Special Type',
                    'view' => 'inventory_special_type_view',
                    'actions' => [],
                ],
            ],
        ],
        [
            'key' => 'stock',
            'label' => 'Stock',
            'view' => 'stock_view',
            'children' => [
                ['key' => 'manage', 'label' => 'Manage Stock', 'view' => 'stock_manage_view', 'actions' => []],
                ['key' => 'adjustment', 'label' => 'Stock Adjustment', 'view' => 'stock_adjustment_view', 'actions' => []],
                ['key' => 'transfer', 'label' => 'Stock Transfer', 'view' => 'stock_transfer_view', 'actions' => []],
            ],
        ],
        [
            'key' => 'sales',
            'label' => 'Sales',
            'view' => 'sales_view',
            'children' => [
                ['key' => 'orders', 'label' => 'Orders', 'view' => 'sales_orders_view', 'actions' => []],
                ['key' => 'invoices', 'label' => 'Invoices', 'view' => 'sales_invoices_view', 'actions' => []],
                ['key' => 'return', 'label' => 'Sales Return', 'view' => 'sales_return_view', 'actions' => []],
            ],
        ],
        [
            'key' => 'promo',
            'label' => 'Promo',
            'view' => 'promo_view',
            'children' => [
                ['key' => 'summary', 'label' => 'Summary', 'view' => 'promo_summary_view', 'actions' => []],
                ['key' => 'code', 'label' => 'Promo Code', 'view' => 'promo_code_view', 'actions' => []],
                ['key' => 'discounts', 'label' => 'Discounts', 'view' => 'promo_discounts_view', 'actions' => []],
            ],
        ],
        [
            'key' => 'finance',
            'label' => 'Finance & Accounts',
            'view' => 'finance_view',
            'children' => [
                ['key' => 'income', 'label' => 'Income', 'view' => 'finance_income_view', 'actions' => []],
                ['key' => 'bank_accounts', 'label' => 'Bank Accounts', 'view' => 'finance_bank_accounts_view', 'actions' => []],
                ['key' => 'money_transfer', 'label' => 'Money Transfer', 'view' => 'finance_money_transfer_view', 'actions' => []],
                ['key' => 'balance_sheet', 'label' => 'Balance Sheet', 'view' => 'finance_balance_sheet_view', 'actions' => []],
            ],
        ],
        [
            'key' => 'peoples',
            'label' => 'Peoples',
            'view' => 'peoples_view',
            'children' => [
                ['key' => 'customers', 'label' => 'Customers', 'view' => 'peoples_customers_view', 'actions' => []],
            ],
        ],
        [
            'key' => 'hrm',
            'label' => 'HRM',
            'view' => 'hrm_view',
            'children' => [
                [
                    'key' => 'employees',
                    'label' => 'Employees',
                    'view' => 'hrm_employees_view',
                    'actions' => [
                        ['name' => 'hrm_employees_create', 'label' => 'Create Employee'],
                        ['name' => 'hrm_employees_update', 'label' => 'Update Employee'],
                        ['name' => 'hrm_employees_delete', 'label' => 'Delete Employee'],
                    ],
                ],
                [
                    'key' => 'roles',
                    'label' => 'Roles',
                    'view' => 'hrm_roles_view',
                    'actions' => [
                        ['name' => 'hrm_roles_create', 'label' => 'Create Role'],
                        ['name' => 'hrm_roles_update', 'label' => 'Update Role'],
                        ['name' => 'hrm_roles_delete', 'label' => 'Delete Role'],
                    ],
                ],
                [
                    'key' => 'shifts',
                    'label' => 'Shifts',
                    'view' => 'hrm_shifts_view',
                    'actions' => [
                        ['name' => 'hrm_shifts_manage', 'label' => 'Manage Shifts'],
                    ],
                ],
                ['key' => 'leaves', 'label' => 'Leaves', 'view' => 'hrm_leaves_view', 'actions' => []],
                ['key' => 'holidays', 'label' => 'Holidays', 'view' => 'hrm_holidays_view', 'actions' => []],
                ['key' => 'cut', 'label' => 'Salary Cut', 'view' => 'hrm_cut_view', 'actions' => []],
                [
                    'key' => 'face_attendance',
                    'label' => 'Face Attendance',
                    'view' => 'hrm_face_attendance_view',
                    'actions' => [
                        ['name' => 'hrm_face_register_view', 'label' => 'Register Face'],
                        ['name' => 'hrm_face_confirm', 'label' => 'Confirm Face Attendance'],
                    ],
                ],
            ],
        ],
        [
            'key' => 'reports',
            'label' => 'Reports',
            'view' => 'reports_view',
            'children' => [
                ['key' => 'sales_report', 'label' => 'Sales', 'view' => 'reports_sales_view', 'actions' => []],
                ['key' => 'inventory_report', 'label' => 'Inventory', 'view' => 'reports_inventory_view', 'actions' => []],
                ['key' => 'invoice_report', 'label' => 'Invoices', 'view' => 'reports_invoice_view', 'actions' => []],
                ['key' => 'customer_report', 'label' => 'Customers', 'view' => 'reports_customer_view', 'actions' => []],
                ['key' => 'product_report', 'label' => 'Products', 'view' => 'reports_product_view', 'actions' => []],
                ['key' => 'profit_loss_report', 'label' => 'Profit & Loss', 'view' => 'reports_profit_loss_view', 'actions' => []],
                ['key' => 'annual_report', 'label' => 'Annual', 'view' => 'reports_annual_view', 'actions' => []],
                ['key' => 'kitchen_report', 'label' => 'Kitchen Performance', 'view' => 'reports_kitchen_view', 'actions' => []],
            ],
        ],
        [
            'key' => 'content',
            'label' => 'Content (CMS)',
            'view' => 'content_view',
            'children' => [
                ['key' => 'select_homepage', 'label' => 'Select Homepage', 'view' => 'content_select_homepage_view', 'actions' => []],
                ['key' => 'homepage_settings', 'label' => 'Homepage Settings', 'view' => 'content_homepage_settings_view', 'actions' => []],
                ['key' => 'font_family', 'label' => 'Font Family', 'view' => 'content_font_family_view', 'actions' => []],
                ['key' => 'auth_layout', 'label' => 'Auth Layout', 'view' => 'content_auth_layout_settings_view', 'actions' => []],
                ['key' => 'select_header', 'label' => 'Select Header', 'view' => 'content_select_header_view', 'actions' => []],
                ['key' => 'header_settings', 'label' => 'Header Settings', 'view' => 'content_header_settings_view', 'actions' => []],
                ['key' => 'top_bar', 'label' => 'Top Bar', 'view' => 'content_top_bar_view', 'actions' => []],
                ['key' => 'footer_settings', 'label' => 'Footer Settings', 'view' => 'content_footer_settings_view', 'actions' => []],
                ['key' => 'pages', 'label' => 'Pages', 'view' => 'content_pages_view', 'actions' => []],
                ['key' => 'appearance', 'label' => 'Appearance', 'view' => 'content_appearance_view', 'actions' => []],
                [
                    'key' => 'events',
                    'label' => 'Events',
                    'view' => 'content_events_view',
                    'actions' => [
                        ['name' => 'create_event', 'label' => 'Create Event'],
                        ['name' => 'delete_event', 'label' => 'Delete Event'],
                    ],
                ],
                [
                    'key' => 'careers',
                    'label' => 'Career',
                    'view' => 'content_careers_view',
                    'actions' => [
                        ['name' => 'career_create', 'label' => 'Create Career'],
                        ['name' => 'career_edit', 'label' => 'Edit Career'],
                        ['name' => 'career_delete', 'label' => 'Delete Career'],
                    ],
                ],
                ['key' => 'about', 'label' => 'About Page', 'view' => 'content_about_view', 'actions' => []],
                ['key' => 'brand_information', 'label' => 'Brand Information', 'view' => 'content_brand_information_view', 'actions' => []],
            ],
        ],
        [
            'key' => 'help',
            'label' => 'Help',
            'view' => 'help_view',
            'children' => [
                ['key' => 'documentation', 'label' => 'Documentation', 'view' => 'help_documentation_view', 'actions' => []],
                ['key' => 'changelog', 'label' => 'Changelog', 'view' => 'help_changelog_view', 'actions' => []],
            ],
        ],
        [
            'key' => 'setup',
            'label' => 'Setup',
            'view' => 'setup_view',
            'children' => [
                ['key' => 'features', 'label' => 'Features Activation', 'view' => 'setup_features_activation_view', 'actions' => []],
                ['key' => 'language', 'label' => 'Language', 'view' => 'setup_language_view', 'actions' => []],
                ['key' => 'vat_tax', 'label' => 'VAT & Tax', 'view' => 'setup_vat_tax_view', 'actions' => []],
                ['key' => 'smtp', 'label' => 'SMTP', 'view' => 'setup_smtp_view', 'actions' => []],
                ['key' => 'order_configuration', 'label' => 'Order Configuration', 'view' => 'setup_order_configuration_view', 'actions' => []],
                ['key' => 'file_cache', 'label' => 'File & Cache', 'view' => 'setup_file_cache_configuration_view', 'actions' => []],
                ['key' => 'company_settings', 'label' => 'Company Settings', 'view' => 'setup_company_settings_view', 'actions' => []],
            ],
        ],
    ],
];
