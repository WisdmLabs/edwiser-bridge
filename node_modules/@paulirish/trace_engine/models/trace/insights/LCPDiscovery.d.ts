import * as Types from '../types/types.js';
import { type InsightModel, type InsightSetContext, type RequiredData } from './types.js';
export declare function deps(): ['NetworkRequests', 'PageLoadMetrics', 'LargestImagePaint', 'Meta'];
export type LCPDiscoveryInsightModel = InsightModel<{
    lcpEvent?: Types.Events.LargestContentfulPaintCandidate;
    shouldRemoveLazyLoading?: boolean;
    shouldIncreasePriorityHint?: boolean;
    shouldPreloadImage?: boolean;
    /** The network request for the LCP image, if there was one. */
    lcpRequest?: Types.Events.SyntheticNetworkRequest;
    earliestDiscoveryTimeTs?: Types.Timing.MicroSeconds;
}>;
export declare function generateInsight(parsedTrace: RequiredData<typeof deps>, context: InsightSetContext): LCPDiscoveryInsightModel;
