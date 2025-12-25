import * as React from "react";
import { CarouselInternalState, CarouselProps, StateCallBack, SkipCallbackOptions } from "./types";
interface DotsTypes {
    props: CarouselProps;
    state: CarouselInternalState;
    goToSlide: (index: number, skipCallbacks?: SkipCallbackOptions) => void;
    getState: () => StateCallBack;
}
declare const Dots: ({ props, state, goToSlide, getState }: DotsTypes) => React.ReactElement<any, string | ((props: any) => React.ReactElement<any, string | any | (new (props: any) => React.Component<any, any, any>)> | null) | (new (props: any) => React.Component<any, any, any>)> | null;
export default Dots;
