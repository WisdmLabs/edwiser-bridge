import { ResponsiveType, CarouselProps } from "../types";
declare function getPartialVisibilityGutter(responsive: ResponsiveType, partialVisible?: boolean, serverSideDeviceType?: string | undefined, clientSideDeviceType?: string | undefined): number | undefined;
declare function getWidthFromDeviceType(deviceType: string, responsive: ResponsiveType): number | string | undefined;
declare function getItemClientSideWidth(props: CarouselProps, slidesToShow: number, containerWidth: number): number;
export { getWidthFromDeviceType, getPartialVisibilityGutter, getItemClientSideWidth };
