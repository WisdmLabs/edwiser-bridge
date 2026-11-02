/// <reference types="react" />
import { CarouselInternalState, CarouselProps, SkipCallbackOptions } from "./types";
interface CarouselItemsProps {
    props: CarouselProps;
    state: CarouselInternalState;
    clones: any[];
    notEnoughChildren: boolean;
    goToSlide: (index: number, skipCallbacks?: SkipCallbackOptions) => void;
}
declare const CarouselItems: ({ props, state, goToSlide, clones, notEnoughChildren }: CarouselItemsProps) => JSX.Element | null;
export default CarouselItems;
