<script setup>
import LobienListingsSection from './LobienListingsSection.vue';

const baseUrl = 'https://www.lobiengroup.com/sites/default/files/properties%20category';

const categories = [
    { label: 'OFFICE & RETAIL FOR LEASE', image: `${baseUrl}/Lobien-Realty_0000s_0000_Office-for-Lease.png`, href: '#' },
    { label: 'RESIDENTIAL FOR LEASE', image: `${baseUrl}/Lobien-Realty_0000s_0002_Residential-for-Lease.png`, href: '#' },
    { label: 'INDUSTRIAL FOR LEASE', image: `${baseUrl}/Lobien-Realty_0000s_0005_Industrial-for-Lease_0.png`, href: '#' },
    { label: 'LOT FOR LEASE', image: `${baseUrl}/Lobien-Realty_0000s_0006_Lot-for-Lease.png`, href: '#' },
    { label: 'OFFICE & RETAIL FOR SALE', image: `${baseUrl}/Lobien-Realty_0000s_0001_Office-for-Sale.png`, href: '#' },
    { label: 'RESIDENTIAL FOR SALE', image: `${baseUrl}/Lobien-Realty_0000s_0003_Residential-for-Sale.png`, href: '#' },
    { label: 'INDUSTRIAL FOR SALE', image: `${baseUrl}/Lobien-Realty_0000s_0004_Industrial-for-Sale.png`, href: '#' },
    { label: 'LOT FOR SALE', image: `${baseUrl}/Lobien-Realty_0000s_0007_Lot-for-Sale.png`, href: '#' },
];
</script>

<template>
    <LobienListingsSection
        heading="Check out our wide list of available properties in the Philippines"
        :items="categories"
    />
</template>
