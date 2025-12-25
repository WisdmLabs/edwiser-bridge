import * as React from "react";
import { StateCallBack } from "./types";
interface LeftArrowProps {
    customLeftArrow?: React.ReactElement<any> | null;
    getState: () => StateCallBack;
    previous: () => void;
    disabled?: boolean;
    rtl?: boolean;
}
interface RightArrowProps {
    customRightArrow?: React.ReactElement<any> | null;
    getState: () => StateCallBack;
    next: () => void;
    disabled?: boolean;
    rtl?: boolean;
}
declare const LeftArrow: ({ customLeftArrow, getState, previous, disabled, rtl }: LeftArrowProps) => React.ReactElement<any, string | ((props: any) => React.ReactElement<any, string | any | (new (props: any) => React.Component<any, any, any>)> | null) | (new (props: any) => React.Component<any, any, any>)>;
declare const RightArrow: ({ customRightArrow, getState, next, disabled, rtl }: RightArrowProps) => React.ReactElement<any, string | ((props: any) => React.ReactElement<any, string | any | (new (props: any) => React.Component<any, any, any>)> | null) | (new (props: any) => React.Component<any, any, any>)>;
export { LeftArrow, RightArrow };
