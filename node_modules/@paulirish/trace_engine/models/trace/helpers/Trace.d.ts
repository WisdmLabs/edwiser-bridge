import type * as Protocol from '../../../generated/protocol.js';
import type * as CPUProfile from '../../cpu_profile/cpu_profile.js';
import * as Types from '../types/types.js';
type MatchedPairType<T extends Types.Events.PairableAsync> = Types.Events.SyntheticEventPair<T>;
type MatchingPairableAsyncEvents = {
    begin: Types.Events.PairableAsyncBegin | null;
    end: Types.Events.PairableAsyncEnd | null;
    instant?: Types.Events.PairableAsyncInstant[];
};
/**
 * Extracts the raw stack trace of known trace events. Most likely than
 * not you want to use `getZeroIndexedStackTraceForEvent`, which returns
 * the stack with zero based numbering. Since some trace events are
 * one based this function can yield unexpected results when used
 * indiscriminately.
 */
export declare function stackTraceForEvent(event: Types.Events.Event): Types.Events.CallFrame[] | null;
export declare function extractOriginFromTrace(firstNavigationURL: string): string | null;
export type EventsInThread<T extends Types.Events.Event> = Map<Types.Events.ThreadID, T[]>;
export declare function addEventToProcessThread<T extends Types.Events.Event>(event: T, eventsInProcessThread: Map<Types.Events.ProcessID, EventsInThread<T>>): void;
export type TimeSpan = {
    ts: Types.Timing.MicroSeconds;
    dur?: Types.Timing.MicroSeconds;
};
export declare function eventTimeComparator(a: TimeSpan, b: TimeSpan): -1 | 0 | 1;
/**
 * Sorts all the events in place, in order, by their start time. If they have
 * the same start time, orders them by longest first.
 */
export declare function sortTraceEventsInPlace(events: {
    ts: Types.Timing.MicroSeconds;
    dur?: Types.Timing.MicroSeconds;
}[]): void;
/**
 * Returns an array of ordered events that results after merging the two
 * ordered input arrays.
 */
export declare function mergeEventsInOrder<T1 extends Types.Events.Event, T2 extends Types.Events.Event>(eventsArray1: readonly T1[], eventsArray2: readonly T2[]): (T1 | T2)[];
export declare function getNavigationForTraceEvent(event: Types.Events.Event, eventFrameId: string, navigationsByFrameId: Map<string, Types.Events.NavigationStart[]>): Types.Events.NavigationStart | null;
export declare function extractId(event: Types.Events.PairableAsync | MatchedPairType<Types.Events.PairableAsync>): string | undefined;
export declare function activeURLForFrameAtTime(frameId: string, time: Types.Timing.MicroSeconds, rendererProcessesByFrame: Map<string, Map<Types.Events.ProcessID, {
    frame: Types.Events.TraceFrame;
    window: Types.Timing.TraceWindowMicroSeconds;
}[]>>): string | null;
/**
 * @param node the node attached to the profile call. Here a node represents a function in the call tree.
 * @param profileId the profile ID that the sample came from that backs this call.
 * @param sampleIndex the index of the sample in the given profile that this call was created from
 * @param ts the timestamp of the profile call
 * @param pid the process ID of the profile call
 * @param tid the thread ID of the profile call
 *
 * See `panels/timeline/docs/profile_calls.md` for more context on how these events are created.
 */
export declare function makeProfileCall(node: CPUProfile.ProfileTreeModel.ProfileNode, profileId: Types.Events.ProfileID, sampleIndex: number, ts: Types.Timing.MicroSeconds, pid: Types.Events.ProcessID, tid: Types.Events.ThreadID): Types.Events.SyntheticProfileCall;
/**
 * Matches beginning events with PairableAsyncEnd and PairableAsyncInstant (ASYNC_NESTABLE_INSTANT)
 * if provided, though currently only coming from Animations. Traces may contain multiple instant events so we need to
 * account for that.
 *
 * @returns {Map<string, MatchingPairableAsyncEvents>} Map of the animation's ID to it's matching events.
 */
export declare function matchEvents(unpairedEvents: Types.Events.PairableAsync[]): Map<string, MatchingPairableAsyncEvents>;
export declare function createSortedSyntheticEvents<T extends Types.Events.PairableAsync>(matchedPairs: Map<string, {
    begin: Types.Events.PairableAsyncBegin | null;
    end: Types.Events.PairableAsyncEnd | null;
    instant?: Types.Events.PairableAsyncInstant[];
}>, syntheticEventCallback?: (syntheticEvent: MatchedPairType<T>) => void): MatchedPairType<T>[];
export declare function createMatchedSortedSyntheticEvents<T extends Types.Events.PairableAsync>(unpairedAsyncEvents: T[], syntheticEventCallback?: (syntheticEvent: MatchedPairType<T>) => void): MatchedPairType<T>[];
/**
 * Different trace events return line/column numbers that are 1 or 0 indexed.
 * This function knows which events return 1 indexed numbers and normalizes
 * them. The UI expects 0 indexed line numbers, so that is what we return.
 */
export declare function getZeroIndexedLineAndColumnForEvent(event: Types.Events.Event): {
    lineNumber?: number;
    columnNumber?: number;
};
/**
 * Different trace events contain stack traces with line/column numbers
 * that are 1 or 0 indexed.
 * This function knows which events return 1 indexed numbers and normalizes
 * them. The UI expects 0 indexed line numbers, so that is what we return.
 */
export declare function getZeroIndexedStackTraceForEvent(event: Types.Events.Event): Types.Events.CallFrame[] | null;
/**
 * Given a 1-based call frame creates a 0-based one.
 */
export declare function makeZeroBasedCallFrame(callFrame: Types.Events.CallFrame): Types.Events.CallFrame;
export declare function frameIDForEvent(event: Types.Events.Event): string | null;
export declare function isTopLevelEvent(event: Types.Events.Event): boolean;
export declare function findUpdateLayoutTreeEvents(events: Types.Events.Event[], startTime: Types.Timing.MicroSeconds, endTime?: Types.Timing.MicroSeconds): Types.Events.UpdateLayoutTree[];
export declare function findNextEventAfterTimestamp<T extends Types.Events.Event>(candidates: T[], ts: Types.Timing.MicroSeconds): T | null;
export declare function findPreviousEventBeforeTimestamp<T extends Types.Events.Event>(candidates: T[], ts: Types.Timing.MicroSeconds): T | null;
export interface ForEachEventConfig {
    onStartEvent: (event: Types.Events.Event) => void;
    onEndEvent: (event: Types.Events.Event) => void;
    onInstantEvent?: (event: Types.Events.Event) => void;
    eventFilter?: (event: Types.Events.Event) => boolean;
    startTime?: Types.Timing.MicroSeconds;
    endTime?: Types.Timing.MicroSeconds;
    ignoreAsyncEvents?: boolean;
}
/**
 * Iterates events in a tree hierarchically, from top to bottom,
 * calling back on every event's start and end in the order
 * dictated by the corresponding timestamp.
 *
 * Events are assumed to be in ascendent order by timestamp.
 *
 * Events with 0 duration are treated as instant events. These do not have a
 * begin and end, but will be passed to the config.onInstantEvent callback as
 * they are discovered. Do not provide this callback if you are not interested
 * in them.
 *
 * For example, given this tree, the following callbacks
 * are expected to be made in the following order
 * |---------------A---------------|
 *  |------B------||-------D------|
 *    |---C---|
 *
 * 1. Start A
 * 3. Start B
 * 4. Start C
 * 5. End C
 * 6. End B
 * 7. Start D
 * 8. End D
 * 9. End A
 *
 * By default, async events are skipped. This behaviour can be
 * overriden making use of the config.ignoreAsyncEvents parameter.
 */
export declare function forEachEvent(events: Types.Events.Event[], config: ForEachEventConfig): void;
export declare function eventHasCategory(event: Types.Events.Event, category: string): boolean;
export declare function nodeIdForInvalidationEvent(event: Types.Events.InvalidationTrackingEvent): Protocol.DOM.BackendNodeId | null;
export {};
