import type * as Types from '../types/types.js';
import { type InsightModel, type InsightSetContext, type RequiredData } from './types.js';
export declare function deps(): ['NetworkRequests', 'Meta', 'ImagePainting'];
export type ImageOptimizationType = 'modern-format-or-compression' | 'compression' | 'video-format' | 'responsive-size';
export interface ImageOptimization {
    type: ImageOptimizationType;
    byteSavings: number;
}
export interface OptimizableImage {
    request: Types.Events.SyntheticNetworkRequest;
    optimizations: ImageOptimization[];
    /**
     * If the an image resource has multiple `PaintImage`s, we compare its intrinsic size to the largest of the displayed sizes.
     *
     * It is theoretically possible for `PaintImage` events with the same URL to have different intrinsic sizes.
     * However, this should be rare because it requires serving different images from the same URL.
     */
    largestImagePaint: Types.Events.PaintImage;
}
export type ImageDeliveryInsightModel = InsightModel<{
    optimizableImages: OptimizableImage[];
}>;
export declare function generateInsight(parsedTrace: RequiredData<typeof deps>, context: InsightSetContext): ImageDeliveryInsightModel;
