import { CarouselInternalState, CarouselProps } from "../types";
interface NextSlidesTable {
    [key: number]: number;
}
declare function getLookupTableForNextSlides(numberOfDotsToShow: number, state: CarouselInternalState, props: CarouselProps, childrenArr: any[]): NextSlidesTable;
export { getLookupTableForNextSlides };
