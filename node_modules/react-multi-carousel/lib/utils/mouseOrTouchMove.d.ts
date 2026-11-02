import { CarouselInternalState, CarouselProps, Direction } from "../types";
declare function populateSlidesOnMouseTouchMove(state: CarouselInternalState, props: CarouselProps, initialX: number, lastX: number, clientX: number, transformPlaceHolder: number): {
    direction?: Direction;
    nextPosition: number | undefined;
    canContinue: boolean;
};
export { populateSlidesOnMouseTouchMove };
