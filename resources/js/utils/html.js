import DOMPurify from 'dompurify';

const SAFE_URL = /^(\/(?!\/)|#|https?:\/\/|mailto:|tel:)/i;

/**
 * Whether a link is safe to put in an href (blocks javascript: and friends).
 */
export function safeUrl(url) {
    return typeof url === 'string' && SAFE_URL.test(url.trim()) ? url.trim() : '';
}

DOMPurify.addHook('uponSanitizeElement', (node, data) => {
    // Only allow embeds (maps, YouTube, forms) served over HTTPS.
    if (data.tagName === 'iframe' && !/^https:\/\//i.test(node.getAttribute('src') || '')) {
        node.remove();
    }
});

DOMPurify.addHook('afterSanitizeAttributes', (node) => {
    if (node.tagName === 'A' && node.getAttribute('target') === '_blank') {
        node.setAttribute('rel', 'noopener noreferrer');
    }
});

/**
 * Clean admin-authored HTML before it is rendered on the site.
 * Styles, classes and HTTPS iframes are kept for design freedom; scripts,
 * event handlers and javascript: URLs are removed so a page editor cannot
 * run code in another admin's session.
 */
export function sanitizeHtml(html) {
    return DOMPurify.sanitize(html || '', {
        FORCE_BODY: true,
        ADD_TAGS: ['style', 'iframe'],
        ADD_ATTR: ['target', 'allow', 'allowfullscreen', 'frameborder', 'loading', 'referrerpolicy'],
        FORBID_TAGS: ['script', 'object', 'embed', 'base', 'meta', 'link', 'form'],
    });
}

/**
 * Split plain text into paragraphs and "- " bullet lists without using HTML.
 */
export function textBlocks(text) {
    return String(text || '')
        .split(/\n\s*\n/)
        .map((block) => block.trim())
        .filter(Boolean)
        .map((block) => {
            const lines = block.split('\n').map((line) => line.trim()).filter(Boolean);

            if (lines.every((line) => line.startsWith('- '))) {
                return { type: 'list', items: lines.map((line) => line.slice(2)) };
            }

            return { type: 'paragraph', text: lines.join('\n') };
        });
}
