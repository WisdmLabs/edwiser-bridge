import * as Helpers from '../helpers/helpers.js';
import * as Types from '../types/types.js';
import type { HandlerName } from './types.js';
export interface ExtensionTraceData {
    extensionTrackData: readonly Types.Extensions.ExtensionTrackData[];
    extensionMarkers: readonly Types.Extensions.SyntheticExtensionMarker[];
    entryToNode: Map<Types.Events.Event, Helpers.TreeHelpers.TraceEntryNode>;
}
export declare function handleEvent(_event: Types.Events.Event): void;
export declare function reset(): void;
export declare function finalize(): Promise<void>;
export declare function extractExtensionEntries(timings: (Types.Events.SyntheticUserTimingPair | Types.Events.PerformanceMark)[]): void;
export declare function extensionDataInTiming(timing: Types.Events.SyntheticUserTimingPair | Types.Events.PerformanceMark): Types.Extensions.ExtensionDataPayload | null;
export declare function data(): ExtensionTraceData;
export declare function deps(): HandlerName[];
