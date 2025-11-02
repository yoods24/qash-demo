<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Permission;

class TenantNavigationPermissionsSeeder extends Seeder
{
    public function run(): void
    {
        if (! function_exists('tenant') || ! tenant('id')) {
            throw new \RuntimeException('TenantNavigationPermissionsSeeder must be executed within a tenant context.');
        }

        $permissions = [
            // POS & Orders
            'pos_view',
            'pos_orders_view',
            'pos_table_orders_view',

            // Kitchen
            'kitchen_view',
            'kitchen_kds_view',
            'kitchen_oss_view',
            'kitchen_kds_update_order',
            'kitchen_kds_confirm_order',
            // Future
            'kitchen_oss_update_order',
            'kitchen_oss_confirm_order',

            // Inventory
            'inventory_view',
            'inventory_products_view',
            'inventory_category_view',
            'inventory_sub_category_view',
            'inventory_special_type_view',

            // Stock
            'stock_view',
            'stock_manage_view',
            'stock_adjustment_view',
            'stock_transfer_view',

            // Sales
            'sales_view',
            'sales_invoices_view',
            'sales_return_view',

            // Promo
            'promo_view',
            'promo_summary_view',
            'promo_code_view',
            'promo_discounts_view',

            // Finance & Accounts
            'finance_view',
            'finance_income_view',
            'finance_bank_accounts_view',
            'finance_money_transfer_view',
            'finance_balance_sheet_view',

            // Peoples
            'peoples_view',
            'peoples_customers_view',

            // HRM
            'hrm_view',
            'hrm_employees_view',
            'hrm_roles_view',
            'hrm_shifts_view',
            'hrm_leaves_view',
            'hrm_holidays_view',
            'hrm_cut_view',

            // Reports
            'reports_view',
            'reports_sales_view',
            'reports_inventory_view',
            'reports_invoice_view',
            'reports_customer_view',
            'reports_product_view',
            'reports_profit_loss_view',
            'reports_annual_view',

            // Content (CMS)
            'content_view',
            'content_select_homepage_view',
            'content_homepage_settings_view',
            'content_font_family_view',
            'content_auth_layout_settings_view',
            'content_select_header_view',
            'content_header_settings_view',
            'content_top_bar_view',
            'content_footer_settings_view',
            'content_pages_view',
            'content_appearance_view',

            // Help
            'help_view',
            'help_documentation_view',
            'help_changelog_view',

            // Setup
            'setup_view',
            'setup_features_activation_view',
            'setup_language_view',
            'setup_vat_tax_view',
            'setup_smtp_view',
            'setup_order_configuration_view',
            'setup_file_cache_configuration_view',
            'setup_company_settings_view',
        ];

        foreach ($permissions as $name) {
            Permission::firstOrCreate(['name' => $name]);
        }
    }
}
