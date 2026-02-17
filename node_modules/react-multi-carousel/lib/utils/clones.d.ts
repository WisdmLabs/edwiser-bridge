import { CarouselInternalState, CarouselProps } from "../types";
declare function getOriginalCounterPart(index: number, { slidesToShow, currentSlide }: {
    slidesToShow: number;
    currentSlide: number;
    totalItems: number;
}, childrenArr: any[]): number;
interface Table {
    [key: number]: number;
}
declare function getOriginalIndexLookupTableByClones(slidesToShow: number, childrenArr: any[]): Table;
declare function getClones(slidesToShow: number, childrenArr: any[]): any[];
declare function getInitialSlideInInfiniteMode(slidesToShow: number, childrenArr: any[]): number;
declare function checkClonesPosition({ currentSlide, slidesToShow, itemWidth, totalItems }: CarouselInternalState, childrenArr: any[], props: CarouselProps): {
    isReachingTheEnd: boolean;
    isReachingTheStart: boolean;
    nextSlide: number;
    nextPosition: number;
};
export { getOriginalCounterPart, getOriginalIndexLookupTableByClones, getClones, checkClonesPosition, getInitialSlideInInfiniteMode };
