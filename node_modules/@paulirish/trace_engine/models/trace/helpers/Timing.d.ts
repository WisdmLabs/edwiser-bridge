import * as Types from '../types/types.js';
export declare const millisecondsToMicroseconds: (value: Types.Timing.MilliSeconds) => Types.Timing.MicroSeconds;
export declare const secondsToMilliseconds: (value: Types.Timing.Seconds) => Types.Timing.MilliSeconds;
export declare const secondsToMicroseconds: (value: Types.Timing.Seconds) => Types.Timing.MicroSeconds;
export declare const microSecondsToMilliseconds: (value: Types.Timing.MicroSeconds) => Types.Timing.MilliSeconds;
export declare const microSecondsToSeconds: (value: Types.Timing.MicroSeconds) => Types.Timing.Seconds;
export declare function timeStampForEventAdjustedByClosestNavigation(event: Types.Events.Event, traceBounds: Types.Timing.TraceWindowMicroSeconds, navigationsByNavigationId: Map<string, Types.Events.NavigationStart>, navigationsByFrameId: Map<string, Types.Events.NavigationStart[]>): Types.Timing.MicroSeconds;
export declare function expandWindowByPercentOrToOneMillisecond(annotationWindow: Types.Timing.TraceWindowMicroSeconds, maxTraceWindow: Types.Timing.TraceWindowMicroSeconds, percentage: number): Types.Timing.TraceWindowMicroSeconds;
export interface EventTimingsData<ValueType extends Types.Timing.MicroSeconds | Types.Timing.MilliSeconds | Types.Timing.Seconds> {
    startTime: ValueType;
    endTime: ValueType;
    duration: ValueType;
}
export declare function eventTimingsMicroSeconds(event: Types.Events.Event): EventTimingsData<Types.Timing.MicroSeconds>;
export declare function eventTimingsMilliSeconds(event: Types.Events.Event): EventTimingsData<Types.Timing.MilliSeconds>;
export declare function eventTimingsSeconds(event: Types.Events.Event): EventTimingsData<Types.Timing.Seconds>;
export declare function traceWindowMilliSeconds(bounds: Types.Timing.TraceWindowMicroSeconds): Types.Timing.TraceWindowMilliSeconds;
export declare function traceWindowMillisecondsToMicroSeconds(bounds: Types.Timing.TraceWindowMilliSeconds): Types.Timing.TraceWindowMicroSeconds;
export declare function traceWindowMicroSecondsToMilliSeconds(bounds: Types.Timing.TraceWindowMicroSeconds): Types.Timing.TraceWindowMilliSeconds;
export declare function traceWindowFromMilliSeconds(min: Types.Timing.MilliSeconds, max: Types.Timing.MilliSeconds): Types.Timing.TraceWindowMicroSeconds;
export declare function traceWindowFromMicroSeconds(min: Types.Timing.MicroSeconds, max: Types.Timing.MicroSeconds): Types.Timing.TraceWindowMicroSeconds;
export declare function traceWindowFromEvent(event: Types.Events.Event): Types.Timing.TraceWindowMicroSeconds;
export interface BoundsIncludeTimeRange {
    timeRange: Types.Timing.TraceWindowMicroSeconds;
    bounds: Types.Timing.TraceWindowMicroSeconds;
}
/**
 * Checks to see if the timeRange is within the bounds. By "within" we mean
 * "has any overlap":
 *         |------------------------|
 *      ==                                     no overlap (entirely before)
 *       =========                             overlap
 *            =========                        overlap
 *                             =========       overlap
 *                                     ====    no overlap (entirely after)
 *        ==============================       overlap (time range is larger than bounds)
 *         |------------------------|
 */
export declare function boundsIncludeTimeRange(data: BoundsIncludeTimeRange): boolean;
/** Checks to see if the event is within or overlaps the bounds */
export declare function eventIsInBounds(event: Types.Events.Event, bounds: Types.Timing.TraceWindowMicroSeconds): boolean;
export declare function timestampIsInBounds(bounds: Types.Timing.TraceWindowMicroSeconds, timestamp: Types.Timing.MicroSeconds): boolean;
export interface WindowFitsInsideBounds {
    window: Types.Timing.TraceWindowMicroSeconds;
    bounds: Types.Timing.TraceWindowMicroSeconds;
}
/**
 * Returns true if the window fits entirely within the bounds.
 * Note that if the window is equivalent to the bounds, that is considered to fit
 */
export declare function windowFitsInsideBounds(data: WindowFitsInsideBounds): boolean;
export declare function windowsEqual(w1: Types.Timing.TraceWindowMicroSeconds, w2: Types.Timing.TraceWindowMicroSeconds): boolean;
