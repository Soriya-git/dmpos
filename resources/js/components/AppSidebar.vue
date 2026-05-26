<script setup lang="ts">
import { Link, usePage } from '@inertiajs/vue3';

import {
    BookOpen,
    Building2,
    LayoutGrid,

    // add icons to the menu
    ShoppingCart,
    ArrowLeftRight,
    ClipboardList,
    PackageCheck,
    Armchair,
    Warehouse,
    PackageSearch,
    Utensils,
    Landmark,
} from 'lucide-vue-next';
import NavFooter from '@/components/NavFooter.vue';
import NavSection from '@/components/NavSection.vue';
import NavUser from '@/components/NavUser.vue';
import {
    Sidebar,
    SidebarContent,
    SidebarFooter,
    SidebarHeader,
    SidebarMenu,
    SidebarMenuButton,
    SidebarMenuItem,
} from '@/components/ui/sidebar';
import { type NavItem, type NavSection as NavSectionType } from '@/types';
import { dashboard } from '@/wayfinder/routes';
import AppLogo from './AppLogo.vue';

const page = usePage();

function canView(permission?: string): boolean {
    const user = page.props.auth.user as
        | {
              roles?: string[];
              permissions?: string[];
          }
        | null
        | undefined;

    if (!permission) return true;
    if (user?.roles?.includes('System Admin')) return true;

    return user?.permissions?.includes(permission) ?? false;
}

function visibleItems(items: NavItem[]): NavItem[] {
    return items.filter((item) => canView(item.permission));
}

function visibleSection(section: NavSectionType): NavSectionType {
    return {
        ...section,
        groups: section.groups
            .map((group) => ({
                ...group,
                items: visibleItems(group.items),
            }))
            .filter((group) => group.items.length > 0),
    };
}

const dashboardNavItems: NavItem[] = [
    {
        title: 'Dashboard',
        href: dashboard().url,
        icon: LayoutGrid,
        permission: 'dashboard.view',
    },
];

const dashboardSection: NavSectionType = {
    label: '',
    groups: [],
};

const operationsNavItems: NavItem[] = [
    {
        title: 'Order',
        href: '/orders',
        icon: Armchair,
        permission: 'orders.view',
    },
];

const OperationsReport: NavSectionType = {
    label: 'Sale Operations',
    groups: [
        {
            title: 'Manage Operations',
            icon: ClipboardList,
            items: [
                {
                    title: 'Stock',
                    href: '/operations-report/daily-session-stock',
                    permission: 'daily-session-stock.view',
                },
                {
                    title: 'Menu',
                    href: '/operations-report/daily-session-menu',
                    permission: 'daily-session-menu.view',
                },
                {
                    title: 'Sales',
                    href: '/sales',
                    permission: 'sales.view',
                },
            ],
        },
    ],
};

const operationsSection: NavSectionType = {
    label: 'Sale Operations',
    groups: [],
};

const stockNavItems: NavItem[] = [
    {
        title: 'Purchase',
        href: '/purchase',
        icon: ShoppingCart,
        permission: 'purchase.view',
    },
    {
        title: 'Goods Receipt',
        href: '/goods-receipts',
        icon: PackageCheck,
        permission: 'goods-receipts.view',
    },
    {
        title: 'Putaway',
        href: '/putaway',
        icon: Warehouse,
        permission: 'putaway.view',
    },
];

const stockSection: NavSectionType = {
    label: 'Stock Operations',
    groups: [
        {
            title: 'Stock Movements',
            icon: ArrowLeftRight,
            items: [
                {
                    title: 'Balance On Hand',
                    href: '/balance-on-hand',
                    permission: 'balance-on-hand.view',
                },
                {
                    title: 'Sale Settlements',
                    href: '/stock-movements/stock-settlements',
                    permission: 'stock-settlements.view',
                },
                {
                    title: 'Stock Adjustments',
                    href: '/stock-movements/stock-adjustments',
                    permission: 'stock-adjustments.view',
                },
                {
                    title: 'Internal Transfer',
                    href: '/stock-movements/internal-transfer',
                    permission: 'internal-transfer.view',
                },
                {
                    title: 'Customer Keep Stock',
                    href: '/stock-customer',
                    permission: 'stock-customer.view',
                },
                {
                    title: 'Stock Write-off',
                    href: '/stock-movements/write-off',
                    permission: 'stock-write-off.view',
                },
            ],
        },
    ],
};

const masterDataSection: NavSectionType = {
    label: 'Master Data',
    groups: [
        {
            title: 'Organizations',
            icon: Building2,
            items: [
                {
                    title: 'Our Company',
                    href: '/master-data/company-branches',
                    permission: 'company-branches.view',
                },
                {
                    title: 'Suppliers',
                    href: '/master-data/suppliers',
                    permission: 'suppliers.view',
                },
                {
                    title: 'Customers',
                    href: '/master-data/customers',
                    permission: 'customers.view',
                },
                {
                    title: 'Membership Cards',
                    href: '/membership-cards',
                    permission: 'membership-cards.view',
                },
            ],
        },
        {
            title: 'Stock Master',
            icon: PackageSearch,
            items: [
                {
                    title: 'Items & BOM',
                    href: '/master-data/products',
                    permission: 'products.view',
                },
                {
                    title: 'Menu',
                    href: '/master-data/menu',
                    permission: 'menu.view',
                },
                {
                    title: 'Warehouse & Location',
                    href: '/master-data/warehouse-locations',
                    permission: 'warehouse-locations.view',
                },
            ],
        },
        {
            title: 'Dinning Resource',
            icon: Utensils,
            items: [
                {
                    title: 'POS',
                    href: '/master-data/pos-terminals',
                    permission: 'pos-terminals.view',
                },
                {
                    title: 'Seats',
                    href: '/master-data/seats',
                    permission: 'seats.view',
                },
            ],
        },
        {
            title: 'Finance',
            icon: Landmark,
            items: [
                {
                    title: 'Exchange Rate',
                    href: '/master-data/exchange-rates',
                    permission: 'exchange-rates.view',
                },
                {
                    title: 'Taxes',
                    href: '/master-data/taxes',
                    permission: 'taxes.view',
                },
                {
                    title: 'Menu Pricelist',
                    href: '/master-data/menu-price-lists',
                    permission: 'menu-price-lists.view',
                },
            ],
        },
    ],
};

// const footerNavItems: NavItem[] = [
//     {
//         title: 'Documentation',
//         href: 'https://inertiajs.com/docs/v3/getting-started',
//         icon: BookOpen,
//     },
// ];
</script>

<template>
    <Sidebar collapsible="icon" variant="inset">
        <SidebarHeader>
            <SidebarMenu>
                <SidebarMenuItem>
                    <SidebarMenuButton size="lg" as-child>
                        <Link :href="dashboard().url">
                            <AppLogo />
                        </Link>
                    </SidebarMenuButton>
                </SidebarMenuItem>
            </SidebarMenu>
        </SidebarHeader>

        <SidebarContent>
            <NavSection
                :section="dashboardSection"
                :items="visibleItems(dashboardNavItems)"
            />

            <NavSection
                :section="visibleSection(OperationsReport)"
                :items="visibleItems(operationsNavItems)"
            />
            <NavSection
                :section="visibleSection(stockSection)"
                :items="visibleItems(stockNavItems)"
            />
            <NavSection :section="visibleSection(masterDataSection)" />
        </SidebarContent>

        <SidebarFooter>
            <!-- <NavFooter :items="footerNavItems" /> -->
            <NavUser />
        </SidebarFooter>
    </Sidebar>
    <slot />
</template>
