<script setup lang="ts">
import { Link } from '@inertiajs/vue3';

import {
    BookOpen,
    Building2,
    LayoutGrid,

    // add icons to the menu
    ShoppingCart,
    ReceiptText,
    ArrowLeftRight,
    MonitorCog,
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

const dashboardNavItems: NavItem[] = [
    {
        title: 'Dashboard',
        href: dashboard().url,
        icon: LayoutGrid,
    },
];

const dashboardSection: NavSectionType = {
    label: '',
    groups: [],
};

const operationsNavItems: NavItem[] = [
    {
        title: 'POS',
        href: '/pos-sessions',
        icon: MonitorCog,
    },
    {
        title: 'Order',
        href: '/orders',
        icon: Armchair,
    },
    {
        title: 'Sales',
        href: '/sales',
        icon: ReceiptText,
    },
];

const operationsSection: NavSectionType = {
    label: 'Sale Operations',
    groups: [],
};

const stockNavItems: NavItem[] = [
    {
        title: 'Purchase',
        href: '/purchase',
        icon: ShoppingCart,
    },
    {
        title: 'Goods Receipt',
        href: '/goods-receipts',
        icon: PackageCheck,
    },
    {
        title: 'Putaway',
        href: '/putaway',
        icon: Warehouse,
    },
];

const stockSection: NavSectionType = {
    label: 'Stock Operations',
    groups: [
        {
            title: 'Stock Movements',
            icon: ArrowLeftRight,
            items: [
                { title: 'Balance On Hand', href: '/balance-on-hand' },
                {
                    title: 'Sale Settlements',
                    href: '/stock-movements/stock-settlements',
                },
                {
                    title: 'Stock Adjustments',
                    href: '/stock-movements/stock-adjustments',
                },
                {
                    title: 'Internal Transfer',
                    href: '/stock-movements/internal-transfer',
                },
                {
                    title: 'Customer Keep Stock',
                    href: '/stock-customer',
                },
                {
                    title: 'Stock Write-off',
                    href: '/stock-movements/write-off',
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
                { title: 'Our Company', href: '/master-data/company-branches' },
                { title: 'Customers', href: '/master-data/customers' },
                { title: 'Suppliers', href: '/master-data/suppliers' },
            ],
        },
        {
            title: 'Stock Master',
            icon: PackageSearch,
            items: [
                { title: 'Items & BOM', href: '/master-data/products' },
                { title: 'Menu', href: '/master-data/menu' },
                {
                    title: 'Warehouse & Location',
                    href: '/master-data/warehouse-locations',
                },
            ],
        },
        {
            title: 'Dinning Resource',
            icon: Utensils,
            items: [
                { title: 'POS', href: '/master-data/pos-terminals' },
                { title: 'Seats', href: '/master-data/seats' },
            ],
        },
        {
            title: 'Finance',
            icon: Landmark,
            items: [
                { title: 'Exchange Rate', href: '/master-data/exchange-rates' },
                { title: 'Taxes', href: '/master-data/taxes' },
                {
                    title: 'Menu Pricelist',
                    href: '/master-data/menu-price-lists',
                },
            ],
        },
    ],
};

const footerNavItems: NavItem[] = [
    {
        title: 'Documentation',
        href: 'https://inertiajs.com/docs/v3/getting-started',
        icon: BookOpen,
    },
];
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
                :items="dashboardNavItems"
            />
            <NavSection
                :section="operationsSection"
                :items="operationsNavItems"
            />
            <NavSection :section="stockSection" :items="stockNavItems" />
            <NavSection :section="masterDataSection" />
        </SidebarContent>

        <SidebarFooter>
            <NavFooter :items="footerNavItems" />
            <NavUser />
        </SidebarFooter>
    </Sidebar>
    <slot />
</template>
