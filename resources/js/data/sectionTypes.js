/**
 * Page-builder section catalogue. Each type lists the fields the admin editor shows;
 * the matching component in components/page-sections renders it on the site.
 * To add a new section type: add an entry here, a renderer, and the type name in PageSection::TYPES.
 */

const BODY_HINT = 'ফাঁকা লাইন দিয়ে প্যারাগ্রাফ আলাদা করুন। "- " দিয়ে শুরু লাইনগুলো বুলেট তালিকা হবে।';
const URL_HINT = 'সাইটের পাতা: /about · বাইরের সাইট: https://…';

const MORE_FIELDS = [
    { key: 'more_label', label: '"সব দেখুন" লিংকের লেখা (ঐচ্ছিক)', type: 'text' },
    { key: 'more_url', label: '"সব দেখুন" লিংক', type: 'text', hint: URL_HINT },
];

/**
 * Extra fields the home editor shows for every section (rows on the home page span the full width).
 */
export const HOME_FIELDS = [
    { key: 'background', label: 'সারির ব্যাকগ্রাউন্ড', type: 'select', options: [['', 'কোনোটি নয়'], ['white', 'সাদা ব্যান্ড'], ['tint', 'হালকা নীল ব্যান্ড']] },
];

const isNoticeWidget = (data) => data.widget === 'recent_notices';
const isFormWidget = (data) => data.widget === 'result_form' || data.widget === 'student_login';
const isLoginWidget = (data) => data.widget === 'student_login';

export const SECTION_TYPES = {
    hero: {
        label: 'হিরো স্লাইডার',
        icon: '◧',
        homeOnly: true,
        description: 'বড় ছবির স্লাইডার, শিরোনাম, বাটন ও পাশে নোটিশ বোর্ড',
        defaults: { badge: '', heading: '', lead: '', show_notices: '1', slides: [{ image: '', alt: '' }], buttons: [{ label: '', url: '', style: 'primary' }] },
        fields: [
            { key: 'badge', label: 'উপরের ছোট ব্যাজ', type: 'text' },
            { key: 'heading', label: 'শিরোনাম', type: 'text', hint: 'খালি রাখলে প্রতিষ্ঠানের নাম দেখাবে।' },
            { key: 'lead', label: 'ছোট বর্ণনা', type: 'textarea', rows: 2 },
            { key: 'show_notices', label: 'পাশে নোটিশ বোর্ড', type: 'select', options: [['1', 'দেখান'], ['0', 'লুকান']] },
            {
                key: 'slides', label: 'স্লাইডের ছবি', type: 'repeater', itemLabel: 'alt', addLabel: '+ ছবি যোগ করুন',
                blank: { image: '', alt: '' },
                fields: [
                    { key: 'image', label: 'ছবি', type: 'image' },
                    { key: 'alt', label: 'ছবির বর্ণনা', type: 'text' },
                ],
            },
            {
                key: 'buttons', label: 'বাটন', type: 'repeater', itemLabel: 'label', addLabel: '+ বাটন যোগ করুন',
                blank: { label: '', url: '', style: 'primary' },
                fields: [
                    { key: 'label', label: 'লেখা', type: 'text' },
                    { key: 'url', label: 'লিংক', type: 'text', hint: URL_HINT },
                    { key: 'style', label: 'ধরন', type: 'select', options: [['primary', 'রঙিন'], ['ghost', 'বর্ডার']] },
                ],
            },
        ],
    },
    intro: {
        label: 'পরিচিতি ও বাণী',
        icon: '❖',
        homeOnly: true,
        description: 'প্রতিষ্ঠানের পরিচিতি, অধ্যক্ষের বাণী ও পাশে লিংক',
        defaults: {
            heading: '', body: '', image: '', link_label: '', link_url: '',
            messages: [{ title: '', name: '', photo: '', text: '', url: '' }],
            show_quick_links: '1', cta_heading: '', cta_text: '', cta_label: '', cta_url: '',
        },
        fields: [
            { key: 'heading', label: 'শিরোনাম', type: 'text' },
            { key: 'body', label: 'লেখা', type: 'textarea', hint: BODY_HINT, rows: 4 },
            { key: 'image', label: 'ছবি', type: 'image' },
            { key: 'link_label', label: 'লিংকের লেখা', type: 'text' },
            { key: 'link_url', label: 'লিংক', type: 'text', hint: URL_HINT },
            {
                key: 'messages', label: 'বাণী', type: 'repeater', itemLabel: 'title', addLabel: '+ বাণী যোগ করুন',
                blank: { title: '', name: '', photo: '', text: '', url: '' },
                fields: [
                    { key: 'title', label: 'শিরোনাম (যেমন: অধ্যক্ষের বাণী)', type: 'text' },
                    { key: 'name', label: 'নাম', type: 'text' },
                    { key: 'photo', label: 'ছবি', type: 'image' },
                    { key: 'text', label: 'সংক্ষিপ্ত বাণী', type: 'textarea', rows: 2 },
                    { key: 'url', label: '"বিস্তারিত" লিংক', type: 'text', hint: URL_HINT },
                ],
            },
            { key: 'show_quick_links', label: 'পাশে গুরুত্বপূর্ণ লিংক', type: 'select', options: [['1', 'দেখান'], ['0', 'লুকান']] },
            { key: 'cta_heading', label: 'পাশের নেভি কার্ডের শিরোনাম', type: 'text', hint: 'শিরোনাম ও বাটন খালি রাখলে কার্ডটি দেখাবে না।' },
            { key: 'cta_text', label: 'নেভি কার্ডের লেখা', type: 'text' },
            { key: 'cta_label', label: 'নেভি কার্ডের বাটন', type: 'text' },
            { key: 'cta_url', label: 'বাটনের লিংক', type: 'text', hint: URL_HINT },
        ],
    },
    text: {
        label: 'লেখা',
        icon: '¶',
        description: 'শিরোনাম, প্যারাগ্রাফ/বুলেট, ছবি ও বাটন',
        defaults: { heading: '', body: '', image: '', image_position: 'top', style: 'card', button_label: '', button_url: '' },
        fields: [
            { key: 'heading', label: 'শিরোনাম', type: 'text' },
            { key: 'body', label: 'লেখা', type: 'textarea', hint: BODY_HINT, rows: 6 },
            { key: 'image', label: 'ছবি (ঐচ্ছিক)', type: 'image' },
            { key: 'image_position', label: 'ছবির অবস্থান', type: 'select', options: [['top', 'উপরে'], ['left', 'বামে'], ['right', 'ডানে']] },
            { key: 'style', label: 'স্টাইল', type: 'select', options: [['card', 'সাদা কার্ড'], ['plain', 'কার্ড ছাড়া'], ['dark', 'নেভি ব্লু কার্ড']] },
            { key: 'button_label', label: 'বাটনের লেখা (ঐচ্ছিক)', type: 'text' },
            { key: 'button_url', label: 'বাটনের লিংক', type: 'text', hint: URL_HINT },
        ],
    },
    cards: {
        label: 'কার্ড গ্রিড',
        icon: '▦',
        description: 'শাখা, বিভাগ, ক্লাব বা লিংকের কার্ড',
        defaults: { heading: '', lead: '', columns: '3', style: 'default', more_label: '', more_url: '', items: [{ title: '', text: '', meta: '', url: '' }] },
        fields: [
            { key: 'heading', label: 'শিরোনাম', type: 'text' },
            { key: 'lead', label: 'ছোট বর্ণনা', type: 'text' },
            { key: 'columns', label: 'প্রতি সারিতে কার্ড', type: 'select', options: [['1', '১টি'], ['2', '২টি'], ['3', '৩টি'], ['4', '৪টি']] },
            { key: 'style', label: 'কার্ডের ধরন', type: 'select', options: [['default', 'সাধারণ কার্ড'], ['numbered', 'নম্বরসহ সেবা কার্ড (০১, ০২…)']] },
            ...MORE_FIELDS,
            {
                key: 'items', label: 'কার্ড', type: 'repeater', itemLabel: 'title', addLabel: '+ কার্ড যোগ করুন',
                blank: { title: '', text: '', meta: '', url: '' },
                fields: [
                    { key: 'title', label: 'শিরোনাম', type: 'text' },
                    { key: 'text', label: 'বর্ণনা', type: 'textarea', rows: 2 },
                    { key: 'meta', label: 'নিচের ছোট লেখা', type: 'text' },
                    { key: 'url', label: 'লিংক (দিলে পুরো কার্ড ক্লিকযোগ্য)', type: 'text', hint: URL_HINT },
                ],
            },
        ],
    },
    people: {
        label: 'ব্যক্তি / শিক্ষক',
        icon: '☺',
        description: 'শিক্ষক তালিকা বা অধ্যক্ষের বাণী',
        defaults: { heading: '', lead: '', style: 'grid', more_label: '', more_url: '', items: [{ name: '', role: '', photo: '', text: '' }] },
        fields: [
            { key: 'heading', label: 'শিরোনাম', type: 'text' },
            { key: 'lead', label: 'ছোট বর্ণনা', type: 'text' },
            { key: 'style', label: 'দেখানোর ধরন', type: 'select', options: [['grid', 'ছবিসহ কার্ড গ্রিড'], ['message', 'বাণী (বড় লেখা)']] },
            ...MORE_FIELDS,
            {
                key: 'items', label: 'ব্যক্তি', type: 'repeater', itemLabel: 'name', addLabel: '+ ব্যক্তি যোগ করুন',
                blank: { name: '', role: '', photo: '', text: '' },
                fields: [
                    { key: 'name', label: 'নাম', type: 'text' },
                    { key: 'role', label: 'পদবি', type: 'text' },
                    { key: 'photo', label: 'ছবি', type: 'image' },
                    { key: 'text', label: 'বাণী / বিবরণ (ঐচ্ছিক)', type: 'textarea', rows: 3 },
                ],
            },
        ],
    },
    table: {
        label: 'টেবিল / তালিকা',
        icon: '☷',
        description: 'রুটিন, ফি, তথ্যের সারি',
        defaults: { heading: '', lead: '', style: 'grid', columns: '', rows: [{ cells: '', link_label: '', link_url: '' }], note: '', more_label: '', more_url: '' },
        fields: [
            { key: 'heading', label: 'শিরোনাম', type: 'text' },
            { key: 'lead', label: 'ছোট বর্ণনা', type: 'text' },
            { key: 'style', label: 'দেখানোর ধরন', type: 'select', options: [['grid', 'টেবিল (নীল হেডার)'], ['list', 'ফি-তালিকা (বাম লেবেল, ডান মান)']] },
            { key: 'columns', label: 'কলামের নাম (টেবিলের জন্য)', type: 'text', hint: '| দিয়ে আলাদা করুন, যেমন: শ্রেণি | শিফট | রুটিন' },
            {
                key: 'rows', label: 'সারি', type: 'repeater', itemLabel: 'cells', addLabel: '+ সারি যোগ করুন',
                blank: { cells: '', link_label: '', link_url: '' },
                fields: [
                    { key: 'cells', label: 'ঘরগুলো', type: 'text', hint: '| দিয়ে আলাদা করুন, যেমন: একাদশ শ্রেণি | সকাল' },
                    { key: 'link_label', label: 'শেষ ঘরের লিংকের লেখা (ঐচ্ছিক)', type: 'text' },
                    { key: 'link_url', label: 'লিংক', type: 'text', hint: URL_HINT },
                ],
            },
            { key: 'note', label: 'নিচের নোট', type: 'text' },
            ...MORE_FIELDS,
        ],
    },
    gallery: {
        label: 'ছবির গ্যালারি',
        icon: '▣',
        description: 'ক্যাপশনসহ ছবি',
        defaults: { heading: '', more_label: '', more_url: '', items: [{ image: '', caption: '' }] },
        fields: [
            { key: 'heading', label: 'শিরোনাম', type: 'text' },
            ...MORE_FIELDS,
            {
                key: 'items', label: 'ছবি', type: 'repeater', itemLabel: 'caption', addLabel: '+ ছবি যোগ করুন',
                blank: { image: '', caption: '' },
                fields: [
                    { key: 'image', label: 'ছবি', type: 'image' },
                    { key: 'caption', label: 'ক্যাপশন', type: 'text' },
                ],
            },
        ],
    },
    band: {
        label: 'নেভি ব্যান্ড (লিংকসহ)',
        icon: '▬',
        description: 'গাঢ় নীল ব্যানার, ডানে লিংক বাটন',
        defaults: { heading: '', text: '', links: [{ label: '', url: '' }] },
        fields: [
            { key: 'heading', label: 'শিরোনাম', type: 'text' },
            { key: 'text', label: 'লেখা', type: 'textarea', rows: 3 },
            {
                key: 'links', label: 'লিংক বাটন', type: 'repeater', itemLabel: 'label', addLabel: '+ লিংক যোগ করুন',
                blank: { label: '', url: '' },
                fields: [
                    { key: 'label', label: 'লেখা', type: 'text' },
                    { key: 'url', label: 'লিংক', type: 'text', hint: URL_HINT },
                ],
            },
        ],
    },
    html: {
        label: 'কাস্টম HTML',
        icon: '</>',
        description: 'নিজের HTML/CSS দিয়ে যেকোনো ডিজাইন',
        defaults: { html: '<div class="card">\n  <h3 class="serif-title">শিরোনাম</h3>\n  <p>এখানে আপনার লেখা…</p>\n</div>' },
        fields: [
            {
                key: 'html', label: 'HTML কোড', type: 'code',
                hint: '<style> ও class/style ব্যবহার করা যাবে। সাইটের ক্লাস যেমন card, btn btn-primary, grid grid-auto-260 কাজ করবে। YouTube/Google Map এর https iframe চলবে। নিরাপত্তার জন্য <script>, onclick ইত্যাদি সরিয়ে ফেলা হয়।',
            },
        ],
    },
    widget: {
        label: 'রেডিমেড উইজেট',
        icon: '⚙',
        description: 'ফলাফল ফর্ম, স্টুডেন্ট লগইন, যোগাযোগ, নোটিশ',
        defaults: { widget: 'contact_info', heading: '', text: '', button_label: '', action_url: '', message: '', link_label: '', link_url: '', limit: '6' },
        fields: [
            {
                key: 'widget', label: 'উইজেট', type: 'select',
                options: [
                    ['contact_info', 'যোগাযোগের তথ্য (সাইট সেটিংস থেকে)'],
                    ['recent_notices', 'সাম্প্রতিক নোটিশ'],
                    ['result_form', 'ফলাফল খোঁজার ফর্ম'],
                    ['student_login', 'স্টুডেন্ট লগইন ফর্ম'],
                ],
            },
            { key: 'heading', label: 'শিরোনাম', type: 'text', hint: 'খালি রাখলে উইজেটের নিজস্ব শিরোনাম দেখাবে।' },
            {
                key: 'limit', label: 'কতটি নোটিশ দেখাবে', type: 'select',
                options: [['4', '৪টি'], ['6', '৬টি'], ['8', '৮টি'], ['10', '১০টি']],
                showIf: isNoticeWidget,
            },
            { key: 'text', label: 'ছোট বর্ণনা', type: 'textarea', rows: 2, showIf: isFormWidget },
            { key: 'button_label', label: 'বাটনের লেখা', type: 'text', showIf: isFormWidget },
            {
                key: 'action_url', label: 'ফর্মের লিংক', type: 'text',
                hint: 'ফলাফল ফর্ম: রোল ও রেজিস্ট্রেশন এই লিংকে পাঠানো হবে। স্টুডেন্ট লগইন: লিংক দিলে ফর্মের বদলে পোর্টালের বাটন দেখাবে। '+URL_HINT,
                showIf: isFormWidget,
            },
            {
                key: 'message', label: 'লিংক না দিলে যে বার্তা দেখাবে', type: 'textarea', rows: 2,
                hint: 'সেবা চালু হওয়ার আগ পর্যন্ত দর্শক এই লেখাটি দেখবে।',
                showIf: isFormWidget,
            },
            { key: 'link_label', label: 'নিচের লিংকের লেখা', type: 'text', showIf: isLoginWidget },
            { key: 'link_url', label: 'নিচের লিংক', type: 'text', hint: URL_HINT, showIf: isLoginWidget },
        ],
    },
};

/**
 * A fresh, independent copy of a section type's default data.
 */
export function blankSection(type) {
    return JSON.parse(JSON.stringify(SECTION_TYPES[type].defaults));
}
