import * as React from "react";
export interface ResponsiveType {
    [key: string]: {
        breakpoint: {
            max: number;
            min: number;
        };
        items: number;
        partialVisibilityGutter?: number;
        paritialVisibilityGutter?: number;
        slidesToSlide?: number;
    };
}
export declare function isMouseMoveEvent(e: React.MouseEvent | React.TouchEvent): e is React.MouseEvent;
export interface CarouselProps {
    responsive: ResponsiveType;
    deviceType?: string;
    ssr?: boolean;
    slidesToSlide?: number;
    draggable?: boolean;
    arrows?: boolean;
    renderArrowsWhenDisabled?: boolean;
    swipeable?: boolean;
    removeArrowOnDeviceType?: string | Array<string>;
    children: any;
    customLeftArrow?: React.ReactElement<any> | null;
    customRightArrow?: React.ReactElement<any> | null;
    customDot?: React.ReactElement<any> | null;
    customButtonGroup?: React.ReactElement<any> | null;
    infinite?: boolean;
    minimumTouchDrag?: number;
    afterChange?: (previousSlide: number, state: StateCallBack) => void;
    beforeChange?: (nextSlide: number, state: StateCallBack) => void;
    sliderClass?: string;
    itemClass?: string;
    itemAriaLabel?: string;
    containerClass?: string;
    className?: string;
    dotListClass?: string;
    keyBoardControl?: boolean;
    centerMode?: boolean;
    autoPlay?: boolean;
    autoPlaySpeed?: number;
    showDots?: boolean;
    renderDotsOutside?: boolean;
    renderButtonGroupOutside?: boolean;
    partialVisible?: boolean;
    partialVisbile?: boolean;
    customTransition?: string;
    transitionDuration?: number;
    focusOnSelect?: boolean;
    additionalTransfrom?: number;
    pauseOnHover?: boolean;
    shouldResetAutoplay?: boolean;
    rewind?: boolean;
    rewindWithAnimation?: boolean;
    rtl?: boolean;
}
export declare type StateCallBack = CarouselInternalState;
export declare type Direction = "left" | "right" | "" | undefined;
export declare type SkipCallbackOptions = boolean | {
    skipBeforeChange?: boolean;
    skipAfterChange?: boolean;
};
export interface ButtonGroupProps {
    previous?: () => void;
    next?: () => void;
    goToSlide?: (index: number, skipCallbacks?: SkipCallbackOptions) => void;
    carouselState?: StateCallBack;
}
export interface ArrowProps {
    onClick?: () => void;
    carouselState?: StateCallBack;
}
export interface DotProps {
    index?: number;
    active?: boolean;
    onClick?: () => void;
    carouselState?: StateCallBack;
}
export interface CarouselInternalState {
    itemWidth: number;
    containerWidth: number;
    slidesToShow: number;
    currentSlide: number;
    totalItems: number;
    domLoaded: boolean;
    deviceType?: string;
    transform: number;
}
export default class Carousel extends React.Component<CarouselProps> {
    previous: (slidesHavePassed: number) => void;
    next: (slidesHavePassed: number) => void;
    goToSlide: (slide: number, skipCallbacks?: SkipCallbackOptions) => void;
    state: CarouselInternalState;
    setClones: (slidesToShow: number, itemWidth?: number, forResizing?: boolean) => void;
    setItemsToShow: (shouldCorrectItemPosition?: boolean) => void;
    correctClonesPosition: ({ domLoaded }: {
        domLoaded: boolean;
    }) => void;
    onMove: boolean;
    direction: Direction;
    containerRef: React.RefObject<HTMLDivElement>;
}
