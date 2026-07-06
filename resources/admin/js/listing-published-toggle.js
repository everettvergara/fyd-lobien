document.addEventListener('DOMContentLoaded', () => {
    const token = document.querySelector('meta[name="csrf-token"]')?.content;

    const syncIconState = (toggle) => {
        const label = document.querySelector(`label[for="${toggle.id}"][data-listing-published-label]`);
        const icon = label?.querySelector('i');

        if (!label || !icon) {
            return;
        }

        label.classList.toggle('text-success', toggle.checked);
        label.classList.toggle('text-secondary', !toggle.checked);
        label.classList.toggle('opacity-50', !toggle.checked);

        const iconClass = toggle.checked ? icon.dataset.publishedIcon : icon.dataset.unpublishedIcon;
        if (iconClass) {
            icon.className = iconClass;
        }
    };

    document.querySelectorAll('[data-listing-published-toggle]').forEach((toggle) => {
        syncIconState(toggle);

        toggle.addEventListener('change', async () => {
            const url = toggle.dataset.url;
            const previous = !toggle.checked;

            syncIconState(toggle);

            if (!url) {
                toggle.checked = previous;
                syncIconState(toggle);

                return;
            }

            try {
                const response = await fetch(url, {
                    method: 'PATCH',
                    headers: {
                        Accept: 'application/json',
                        'Content-Type': 'application/json',
                        ...(token ? { 'X-CSRF-TOKEN': token } : {}),
                    },
                    body: JSON.stringify({
                        published_to_public: toggle.checked,
                    }),
                });

                if (!response.ok) {
                    throw new Error('Request failed');
                }

                if (typeof window.showToast === 'function') {
                    window.showToast('Listing publication updated.');
                }
            } catch (error) {
                toggle.checked = previous;
                syncIconState(toggle);

                if (typeof window.showToast === 'function') {
                    window.showToast('Unable to update publication status.', 'error');
                }
            }
        });
    });
});
