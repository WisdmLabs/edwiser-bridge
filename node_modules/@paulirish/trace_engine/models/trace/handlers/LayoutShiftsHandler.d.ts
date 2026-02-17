import type * as Protocol from '../../../generated/protocol.js';
import * as Types from '../types/types.js';
import { ScoreClassification } from './PageLoadMetricsHandler.js';
import type { HandlerName } from './types.js';
interface LayoutShifts {
    clusters: readonly Types.Events.SyntheticLayoutShiftCluster[];
    clustersByNavigationId: Map<Types.Events.NavigationId, Types.Events.SyntheticLayoutShiftCluster[]>;
    sessionMaxScore: number;
    clsWindowID: number;
    prePaintEvents: Types.Events.PrePaint[];
    paintImageEvents: Types.Events.PaintImage[];
    layoutInvalidationEvents: readonly Types.Events.LayoutInvalidationTracking[];
    scheduleStyleInvalidationEvents: readonly Types.Events.ScheduleStyleInvalidationTracking[];
    styleRecalcInvalidationEvents: readonly Types.Events.StyleRecalcInvalidationTracking[];
    renderFrameImplCreateChildFrameEvents: readonly Types.Events.RenderFrameImplCreateChildFrame[];
    domLoadingEvents: readonly Types.Events.DomLoading[];
    layoutImageUnsizedEvents: readonly Types.Events.LayoutImageUnsized[];
    beginRemoteFontLoadEvents: readonly Types.Events.BeginRemoteFontLoad[];
    scoreRecords: readonly ScoreRecord[];
    backendNodeIds: Protocol.DOM.BackendNodeId[];
}
export declare const MAX_CLUSTER_DURATION: Types.Timing.MicroSeconds;
export declare const MAX_SHIFT_TIME_DELTA: Types.Timing.MicroSeconds;
type ScoreRecord = {
    ts: number;
    score: number;
};
export declare function reset(): void;
export declare function handleEvent(event: Types.Events.Event): void;
export declare function finalize(): Promise<void>;
export declare function data(): LayoutShifts;
export declare function deps(): HandlerName[];
export declare function scoreClassificationForLayoutShift(score: number): ScoreClassification;
export declare const enum LayoutShiftsThreshold {
    GOOD = 0,
    NEEDS_IMPROVEMENT = 0.1,
    BAD = 0.25
}
export {};
