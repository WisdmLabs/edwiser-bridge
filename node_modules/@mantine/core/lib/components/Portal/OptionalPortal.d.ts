import { PortalProps } from './Portal';
export interface OptionalPortalProps extends PortalProps {
    /** Determines whether children should be rendered inside `<Portal />` */
    withinPortal?: boolean;
}
export declare function OptionalPortal({ withinPortal, children, ...others }: OptionalPortalProps): import("react/jsx-runtime").JSX.Element;
export declare namespace OptionalPortal {
    var displayName: string;
}
