const VIDEO_ID_PATTERN = '([A-Za-z0-9_-]{11})';

const NON_VIDEO_YOUTUBE_PATTERN = /youtube\.com\/(?:hashtag|channel)\/|youtube\.com\/@/i;

const URL_IN_TEXT_PATTERN = /https?:\/\/(?:www\.)?(?:youtube(?:-nocookie)?\.com\/[^\s"'<>]+|youtu\.be\/[^\s"'<>]+)/gi;

/**
 * @param {string} url
 * @returns {boolean}
 */
function isNonVideoYoutubeUrl(url) {
    return NON_VIDEO_YOUTUBE_PATTERN.test(url);
}

/**
 * @param {string|null|undefined} url
 * @returns {string|null} YouTube video id
 */
export function youtubeVideoId(url) {
    if (typeof url !== 'string' || url.trim() === '') {
        return null;
    }

    const value = url.trim();

    if (isNonVideoYoutubeUrl(value)) {
        return null;
    }

    const pathPatterns = [
        new RegExp(`(?:youtube\\.com/watch\\?(?:[^#]*&)?v=|youtube\\.com/embed/|youtube\\.com/shorts/|youtube\\.com/live/|youtube\\.com/v/|youtube-nocookie\\.com/embed/|youtu\\.be/)${VIDEO_ID_PATTERN}`, 'i'),
    ];

    for (const pattern of pathPatterns) {
        const match = value.match(pattern);

        if (match?.[1]) {
            return match[1];
        }
    }

    if (/youtube(?:-nocookie)?\.com|youtu\.be/i.test(value)) {
        const queryMatch = value.match(/[?&]v=([A-Za-z0-9_-]{11})/i);

        if (queryMatch?.[1]) {
            return queryMatch[1];
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

/**
 * @param {string|null|undefined} text
 * @returns {string|null}
 */
export function extractYoutubeUrlFromText(text) {
    if (typeof text !== 'string' || text.trim() === '') {
        return null;
    }

    const matches = text.match(URL_IN_TEXT_PATTERN) ?? [];

    for (const candidate of matches) {
        const embedUrl = youtubeEmbedUrl(candidate.replace(/[.,);]+$/, ''));

        if (embedUrl) {
            return embedUrl;
        }
    }

    return null;
}

/**
 * @param {...(string|null|undefined)} sources
 * @returns {string|null}
 */
export function resolveYoutubeEmbedUrl(...sources) {
    for (const source of sources) {
        const direct = youtubeEmbedUrl(source);

        if (direct) {
            return direct;
        }

        const extracted = extractYoutubeUrlFromText(source);

        if (extracted) {
            return extracted;
        }
    }

    return null;
}
