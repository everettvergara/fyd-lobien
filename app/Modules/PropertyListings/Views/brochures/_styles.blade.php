<style>
    :root {
        --brochure-navy: #0a1136;
        --brochure-red: #e32526;
        --brochure-label-blue: #0a1136;
        --brochure-hex-side: 2.75rem;
        --brochure-hex-height: calc(var(--brochure-hex-side) * 2);
        --brochure-hex-width: calc(var(--brochure-hex-side) * 1.7320508);
        --brochure-hex-overlap: calc(var(--brochure-hex-width) * 0.5);
    }

    .listing-brochure-canvas {
        background: #e9ecef;
        padding: 1.5rem 1rem 2rem;
        min-height: 60vh;
    }

    .listing-brochure-document {
        max-width: 210mm;
        margin: 0 auto;
    }

    .listing-brochure-page {
        background: #fff;
        width: 210mm;
        min-height: 297mm;
        margin: 0 auto 1.5rem;
        box-shadow: 0 0.25rem 1rem rgba(0, 0, 0, 0.12);
        display: flex;
        flex-direction: column;
        page-break-after: always;
        overflow: hidden;
    }

    .listing-brochure-page:last-child {
        margin-bottom: 0;
    }

    .listing-brochure-header {
        display: flex;
        align-items: flex-start;
        height: var(--brochure-hex-height);
        padding: 0.5rem 0 0 0.75rem;
        box-sizing: content-box;
    }

    .listing-brochure-header__hexagon-stack {
        position: relative;
        flex: 0 0 auto;
        width: var(--brochure-hex-width);
        height: var(--brochure-hex-height);
        z-index: 2;
    }

    .listing-brochure-header__hexagon-backing {
        position: absolute;
        inset: 0;
        background: #fff;
        clip-path: polygon(50% 0%, 100% 25%, 100% 75%, 50% 100%, 0% 75%, 0% 25%);
    }

    .listing-brochure-header__site-logo {
        position: absolute;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%);
        max-width: 62%;
        max-height: 62%;
        object-fit: contain;
        z-index: 1;
    }

    .listing-brochure-header__logo-fallback {
        position: absolute;
        inset: 0;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: 700;
        color: var(--brochure-navy);
        font-size: 1.5rem;
        z-index: 1;
    }

    .listing-brochure-header__hexagon-frame {
        position: absolute;
        inset: 0;
        width: 100%;
        height: 100%;
        object-fit: contain;
        z-index: 2;
        pointer-events: none;
    }

    .listing-brochure-header__banner {
        flex: 1 1 auto;
        height: var(--brochure-hex-side);
        margin-top: calc(var(--brochure-hex-side) / 2);
        margin-left: calc(var(--brochure-hex-overlap) * -1);
        background: var(--brochure-navy);
        display: flex;
        align-items: center;
        padding: 0 1.25rem 0 calc(var(--brochure-hex-overlap) + 1rem);
        z-index: 1;
        -webkit-print-color-adjust: exact;
        print-color-adjust: exact;
    }

    .listing-brochure-header__title {
        color: #fff;
        font-size: 1.35rem;
        font-weight: 700;
        letter-spacing: 0.02em;
        line-height: 1.2;
        margin: 0;
        text-transform: uppercase;
    }

    .listing-brochure-body {
        flex: 1 1 auto;
        padding: 1.25rem 1.5rem 1rem;
    }

    .listing-brochure-footer {
        padding: 0.75rem 1.5rem 1rem;
        text-align: center;
    }

    .listing-brochure-footer__text {
        margin: 0;
        font-size: 0.7rem;
        color: #9ca3af;
    }

    .listing-brochure-section-bar {
        background: var(--brochure-red);
        color: #fff;
        font-weight: 700;
        font-size: 0.85rem;
        letter-spacing: 0.04em;
        padding: 0.45rem 0.75rem;
        text-align: center;
        text-transform: uppercase;
        margin-bottom: 0.75rem;
    }

    .listing-brochure-section-bar--left {
        text-align: left;
    }

    .listing-brochure-images {
        display: flex;
        flex-wrap: wrap;
        gap: 0.75rem;
        align-items: center;
        justify-content: center;
        min-height: 12rem;
    }

    .listing-brochure-images--hero .listing-brochure-image-frame {
        flex: 1 1 100%;
        max-height: 70vh;
    }

    .listing-brochure-images--pair .listing-brochure-image-frame {
        flex: 1 1 calc(50% - 0.5rem);
        max-height: 55vh;
    }

    .listing-brochure-images--triple .listing-brochure-image-frame {
        flex: 1 1 calc(33.333% - 0.5rem);
        max-height: 45vh;
    }

    .listing-brochure-images--grid-2x2 .listing-brochure-image-frame {
        flex: 1 1 calc(50% - 0.5rem);
        max-height: 38vh;
    }

    .listing-brochure-images--stack {
        flex-direction: column;
    }

    .listing-brochure-images--stack .listing-brochure-image-frame {
        flex: 1 1 auto;
        width: 100%;
        max-height: 42vh;
    }

    .listing-brochure-images--cinematic {
        position: relative;
        min-height: 16rem;
        overflow: hidden;
        border-radius: 0.25rem;
    }

    .listing-brochure-images--cinematic .listing-brochure-image-frame {
        position: relative;
        z-index: 1;
        flex: 1 1 100%;
        max-height: 55vh;
        background: transparent;
        border: none;
        box-shadow: none;
    }

    .listing-brochure-images--cinematic::before,
    .listing-brochure-images--cinematic::after {
        content: '';
        position: absolute;
        top: 0;
        bottom: 0;
        width: 18%;
        background-size: cover;
        background-position: center;
        filter: blur(8px);
        opacity: 0.55;
        z-index: 0;
    }

    .listing-brochure-images--cinematic::before {
        left: 0;
    }

    .listing-brochure-images--cinematic::after {
        right: 0;
    }

    .listing-brochure-images--plan .listing-brochure-image-frame {
        flex: 1 1 100%;
        max-height: 65vh;
        padding: 1rem;
    }

    .listing-brochure-image-frame {
        background: #fff;
        border: 1px solid #dee2e6;
        box-shadow: 0 0.125rem 0.35rem rgba(0, 0, 0, 0.08);
        display: flex;
        align-items: center;
        justify-content: center;
        overflow: hidden;
        min-height: 8rem;
    }

    .listing-brochure-image-frame img {
        display: block;
        max-width: 100%;
        max-height: 100%;
        width: auto;
        height: auto;
        object-fit: contain;
    }

    .listing-brochure-placeholder {
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        gap: 0.5rem;
        min-height: 14rem;
        color: #adb5bd;
        text-align: center;
        padding: 2rem;
    }

    .listing-brochure-placeholder i {
        font-size: 2.5rem;
    }

    .listing-brochure-units-table {
        width: 100%;
        border-collapse: collapse;
        font-size: 0.68rem;
    }

    .listing-brochure-units-table th,
    .listing-brochure-units-table td {
        border: 1px solid #ced4da;
        padding: 0.3rem 0.35rem;
        vertical-align: top;
    }

    .listing-brochure-units-table thead th {
        font-weight: 700;
        background: #fff;
    }

    .listing-brochure-units-layout {
        display: flex;
        gap: 1rem;
        align-items: flex-start;
    }

    .listing-brochure-units-main {
        flex: 1 1 auto;
        min-width: 0;
    }

    .listing-brochure-other-rates {
        flex: 0 0 11rem;
    }

    .listing-brochure-other-rates__list {
        font-size: 0.72rem;
        margin: 0;
        padding: 0;
        list-style: none;
    }

    .listing-brochure-other-rates__list li {
        display: flex;
        justify-content: space-between;
        gap: 0.5rem;
        padding: 0.2rem 0;
        border-bottom: 1px solid #eee;
    }

    .listing-brochure-notes {
        margin-top: 1rem;
        font-size: 0.65rem;
        color: #495057;
    }

    .listing-brochure-handover-key {
        font-style: italic;
        margin-bottom: 0.35rem;
    }

    .listing-brochure-disclaimer__list {
        font-style: italic;
        padding-left: 1rem;
    }

    .listing-brochure-info-layout {
        display: grid;
        grid-template-columns: 42% 1fr;
        gap: 1rem;
        align-items: start;
    }

    .listing-brochure-info-images .listing-brochure-image-frame {
        margin-bottom: 0.75rem;
        min-height: 10rem;
    }

    .listing-brochure-info-table {
        width: 100%;
        border-collapse: collapse;
        font-size: 0.72rem;
    }

    .listing-brochure-info-table td {
        border-bottom: 1px solid #e9ecef;
        padding: 0.35rem 0;
        vertical-align: top;
    }

    .listing-brochure-info-table td:first-child {
        color: var(--brochure-label-blue);
        font-weight: 700;
        width: 52%;
        padding-right: 0.75rem;
    }

    .listing-brochure-hub-card {
        height: 100%;
    }

    @media (max-width: 767.98px) {
        .listing-brochure-page {
            width: 100%;
            min-height: auto;
        }

        .listing-brochure-info-layout {
            grid-template-columns: 1fr;
        }

        .listing-brochure-units-layout {
            flex-direction: column;
        }

        .listing-brochure-other-rates {
            flex-basis: auto;
            width: 100%;
        }
    }

    @media print {
        @page {
            size: A4 portrait;
            margin: 0;
        }

        body * {
            visibility: hidden;
        }

        .listing-brochure-document,
        .listing-brochure-document * {
            visibility: visible;
        }

        .listing-brochure-document {
            position: absolute;
            left: 0;
            top: 0;
            width: 100%;
            max-width: none;
        }

        .listing-brochure-canvas {
            background: none;
            padding: 0;
            min-height: 0;
        }

        .listing-brochure-page {
            width: 100%;
            min-height: 297mm;
            margin: 0;
            box-shadow: none;
            page-break-after: always;
            -webkit-print-color-adjust: exact;
            print-color-adjust: exact;
        }

        .listing-brochure-page:last-child {
            page-break-after: auto;
        }

        .admin-sidebar,
        .admin-topbar,
        .listing-brochure-preview,
        .admin-breadcrumbs,
        .admin-page-header {
            display: none !important;
        }

        .admin-main-content,
        .admin-content-wrapper,
        .admin-layout {
            margin: 0 !important;
            padding: 0 !important;
        }
    }
</style>
