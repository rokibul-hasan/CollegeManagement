const DIGITS = ['০', '১', '২', '৩', '৪', '৫', '৬', '৭', '৮', '৯'];

const MONTHS = [
    'জানুয়ারি', 'ফেব্রুয়ারি', 'মার্চ', 'এপ্রিল', 'মে', 'জুন',
    'জুলাই', 'আগস্ট', 'সেপ্টেম্বর', 'অক্টোবর', 'নভেম্বর', 'ডিসেম্বর',
];

/**
 * Replace western digits with Bengali digits.
 */
export function bnDigits(value) {
    return String(value ?? '').replace(/\d/g, (digit) => DIGITS[digit]);
}

function parts(isoDate) {
    const [year, month, day] = String(isoDate ?? '').slice(0, 10).split('-').map(Number);

    return { year, month, day };
}

/**
 * 2026-09-15 → ১৫ সেপ্টেম্বর ২০২৬
 */
export function bnDate(isoDate) {
    const { year, month, day } = parts(isoDate);

    if (!year) {
        return '';
    }

    return `${bnDigits(String(day).padStart(2, '0'))} ${MONTHS[month - 1]} ${bnDigits(year)}`;
}

/**
 * 2026-09-15 → ১৫/০৯
 */
export function bnShortDate(isoDate) {
    const { month, day } = parts(isoDate);

    if (!month) {
        return '';
    }

    return bnDigits(`${String(day).padStart(2, '0')}/${String(month).padStart(2, '0')}`);
}
