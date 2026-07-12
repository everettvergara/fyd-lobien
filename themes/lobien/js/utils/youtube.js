/**
 * @param {string|null|undefined} url
 * @returns {string|null} YouTube video id
 */
export function youtubeVideoId(url) {
    if (typeof url !== 'string' || url.trim() === '') {
        return null;
    }

    const value = url.trim();

    const patterns = [
        /(?:youtube\.com\/watch\?(?:[^#]*&)?v=|youtube\.com\/embed\/|youtube\.com\/shorts\/|youtu\.be\/)([A-Za-z0-9_-]{11})/i,
    ];

    for (const pattern of patterns) {
        const match = value.match(pattern);

        if (match?.[1]) {
            return match[1];
        }
    }

    return null;
}

/**
 * @param {string|null|undefined} url
 * @returns {string|null}
 */
export function youtubeEmbedUrl(url) {
    const id = youtubeVideoId(url);

    if (!id) {
        return null;
    }

    return `https://www.youtube.com/embed/${id}`;
}

/**
 * @param {string|null|undefined} url
 * @returns {string|null}
 */
export function youtubeThumbnailUrl(url) {
    const id = youtubeVideoId(url);

    if (!id) {
        return null;
    }

    return `https://i.ytimg.com/vi/${id}/hqdefault.jpg`;
}
