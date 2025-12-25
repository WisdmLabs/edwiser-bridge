import * as Types from '../types/types.js';
import { type InsightModel, type InsightSetContext, type RequiredData } from './types.js';
export declare function deps(): ['NetworkRequests', 'PageLoadMetrics', 'LargestImagePaint', 'Meta'];
interface LCPPhases {
    /**
     * The time between when the user initiates loading the page until when
     * the browser receives the first byte of the html response.
     */
    ttfb: Types.Timing.MilliSeconds;
    /**
     * The time between ttfb and the LCP request request being started.
     * For a text LCP, this is undefined given no request is loaded.
     */
    loadDelay?: Types.Timing.MilliSeconds;
    /**
     * The time it takes to load the LCP request.
     */
    loadTime?: Types.Timing.MilliSeconds;
    /**
     * The time between when the LCP request finishes loading and when
     * the LCP element is rendered.
     */
    renderDelay: Types.Timing.MilliSeconds;
}
export type LCPPhasesInsightModel = InsightModel<{
    lcpMs?: Types.Timing.MilliSeconds;
    lcpTs?: Types.Timing.MilliSeconds;
    lcpEvent?: Types.Events.LargestContentfulPaintCandidate;
    /** The network request for the LCP image, if there was one. */
    lcpRequest?: Types.Events.SyntheticNetworkRequest;
    phases?: LCPPhases;
}>;
export declare function generateInsight(parsedTrace: RequiredData<typeof deps>, context: InsightSetContext): LCPPhasesInsightModel;
export {};
