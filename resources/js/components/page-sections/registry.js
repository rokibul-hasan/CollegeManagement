import HeroSection from './HeroSection.vue';
import IntroSection from './IntroSection.vue';
import TextSection from './TextSection.vue';
import CardsSection from './CardsSection.vue';
import PeopleSection from './PeopleSection.vue';
import TableSection from './TableSection.vue';
import GallerySection from './GallerySection.vue';
import BandSection from './BandSection.vue';
import HtmlSection from './HtmlSection.vue';
import WidgetSection from './WidgetSection.vue';

/**
 * Renderer for each section type, shared by inner pages and the home page.
 */
export const SECTION_COMPONENTS = {
    hero: HeroSection,
    intro: IntroSection,
    text: TextSection,
    cards: CardsSection,
    people: PeopleSection,
    table: TableSection,
    gallery: GallerySection,
    band: BandSection,
    html: HtmlSection,
    widget: WidgetSection,
};
