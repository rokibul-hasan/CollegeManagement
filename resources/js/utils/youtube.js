const ID_PATTERN = /(?:youtube\.com\/(?:watch\?(?:.*&)?v=|shorts\/|live\/|embed\/)|youtu\.be\/)([A-Za-z0-9_-]{11})/;

/**
 * The 11-character video id from any common YouTube link, or '' when it is not one.
 */
export function youtubeId(url) {
    return String(url || '').match(ID_PATTERN)?.[1] ?? '';
}

export function youtubeThumbnail(id) {
    return `https://i.ytimg.com/vi/${id}/hqdefault.jpg`;
}

/**
 * Privacy-enhanced embed URL that starts playing as soon as it opens.
 */
export function youtubeEmbed(id) {
    return `https://www.youtube-nocookie.com/embed/${id}?autoplay=1&rel=0&modestbranding=1`;
}
