import { computed } from 'vue';
import { usePage } from '@inertiajs/vue3';

const socialNetworks = [
    { key: 'facebook', icon: 'bi-facebook', fallback: 'https://www.facebook.com/', label: 'Facebook' },
    { key: 'instagram', icon: 'bi-instagram', fallback: 'https://www.instagram.com/', label: 'Instagram' },
    { key: 'linkedin', icon: 'bi-linkedin', fallback: 'https://www.linkedin.com/', label: 'LinkedIn' },
    { key: 'tiktok', icon: 'bi-tiktok', fallback: 'https://www.tiktok.com/', label: 'TikTok' },
    { key: 'youtube', icon: 'bi-youtube', fallback: 'https://www.youtube.com/', label: 'YouTube' },
];

export function useSocialLinks() {
    const page = usePage();
    const social = computed(() => page.props.app?.social ?? {});

    const socialLinks = computed(() => socialNetworks.map(({ key, icon, fallback, label }) => ({
        key,
        icon,
        label,
        url: social.value[key]?.trim() || fallback,
    })));

    return { socialLinks };
}
