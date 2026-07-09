function cellValue(row, field) {
    const cell = row.find((item) => item.field === field);

    return cell?.value ?? null;
}

function rowCell(row, field) {
    return row.find((item) => item.field === field) ?? null;
}

function cellContentHref(cell) {
    if (cell?.linkToContent && cell?.contentPath) {
        return `/${cell.contentPath}`;
    }

    return null;
}

function rowPath(row, pathPrefix) {
    const linkedCell = row.find((item) => item.linkToContent && item.contentPath)
        ?? row.find((item) => item.contentPath);

    if (linkedCell?.contentPath) {
        return linkedCell.contentPath;
    }

    const slug = cellValue(row, 'slug');

    if (slug) {
        return pathPrefix ? `${pathPrefix}/${slug}` : slug;
    }

    const title = cellValue(row, 'title');

    if (typeof title === 'string' && title !== '') {
        const slugified = title
            .toLowerCase()
            .replace(/[^a-z0-9\s-]/g, '')
            .trim()
            .replace(/\s+/g, '-')
            .replace(/-+/g, '-');

        if (slugified && pathPrefix) {
            return `${pathPrefix}/${slugified}`;
        }
    }

    return null;
}

/**
 * @param {object|null|undefined} contentBlock
 * @param {string|null} pathPrefix e.g. "articles" for article listings
 * @returns {Array<{title: string, summary: string|null, publishedAt: string|null, author: string|null, featuredImage: object|null, path: string|null, titleHref: string|null, imageHref: string|null, urlLink: string|null}>}
 */
export function mapContentBlockRowsToArticles(contentBlock, pathPrefix = null) {
    const rows = contentBlock?.rows ?? [];

    return rows.map((row) => ({
        title: cellValue(row, 'title') ?? '',
        summary: cellValue(row, 'summary'),
        publishedAt: cellValue(row, 'published_at'),
        author: cellValue(row, 'author.name'),
        featuredImage: cellValue(row, 'featured_image'),
        path: rowPath(row, pathPrefix),
        titleHref: cellContentHref(rowCell(row, 'title')),
        imageHref: cellContentHref(rowCell(row, 'featured_image')),
        urlLink: cellValue(row, 'url_link'),
    })).filter((article) => article.title !== '');
}
