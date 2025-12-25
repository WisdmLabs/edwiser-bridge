// Copyright 2024 The Chromium Authors. All rights reserved.
// Use of this source code is governed by a BSD-style license that can be
// found in the LICENSE file.
import * as Helpers from '../helpers/helpers.js';
import * as Types from '../types/types.js';
import { data as userTimingsData } from './UserTimingsHandler.js';
const extensionFlameChartEntries = [];
const extensionTrackData = [];
const extensionMarkers = [];
const entryToNode = new Map();
export function handleEvent(_event) {
    // Implementation not needed because data is sourced from UserTimingsHandler
}
export function reset() {
    extensionFlameChartEntries.length = 0;
    extensionTrackData.length = 0;
    extensionMarkers.length = 0;
    entryToNode.clear();
}
export async function finalize() {
    createExtensionFlameChartEntries();
}
function createExtensionFlameChartEntries() {
    const pairedMeasures = userTimingsData().performanceMeasures;
    const marks = userTimingsData().performanceMarks;
    const mergedRawExtensionEvents = Helpers.Trace.mergeEventsInOrder(pairedMeasures, marks);
    extractExtensionEntries(mergedRawExtensionEvents);
    Helpers.Extensions.buildTrackDataFromExtensionEntries(extensionFlameChartEntries, extensionTrackData, entryToNode);
}
export function extractExtensionEntries(timings) {
    for (const timing of timings) {
        const extensionPayload = extensionDataInTiming(timing);
        if (!extensionPayload) {
            // Not an extension user timing.
            continue;
        }
        const extensionSyntheticEntry = {
            name: timing.name,
            ph: "X" /* Types.Events.Phase.COMPLETE */,
            pid: Types.Events.ProcessID(0),
            tid: Types.Events.ThreadID(0),
            ts: timing.ts,
            dur: timing.dur,
            cat: 'devtools.extension',
            args: extensionPayload,
            rawSourceEvent: Types.Events.isSyntheticUserTiming(timing) ? timing.rawSourceEvent : timing,
        };
        if (Types.Extensions.isExtensionPayloadMarker(extensionPayload)) {
            const extensionMarker = Helpers.SyntheticEvents.SyntheticEventsManager.getActiveManager()
                .registerSyntheticEvent(extensionSyntheticEntry);
            extensionMarkers.push(extensionMarker);
            continue;
        }
        if (Types.Extensions.isExtensionPayloadTrackEntry(extensionSyntheticEntry.args)) {
            const extensionTrackEntry = Helpers.SyntheticEvents.SyntheticEventsManager.getActiveManager()
                .registerSyntheticEvent(extensionSyntheticEntry);
            extensionFlameChartEntries.push(extensionTrackEntry);
            continue;
        }
    }
}
export function extensionDataInTiming(timing) {
    const timingDetail = Types.Events.isPerformanceMark(timing) ? timing.args.data?.detail : timing.args.data.beginEvent.args.detail;
    if (!timingDetail) {
        return null;
    }
    try {
        // Attempt to parse the detail as an object that might be coming from a
        // DevTools Perf extension.
        // Wrapped in a try-catch because timingDetail might either:
        // 1. Not be `json.parse`-able (it should, but just in case...)
        // 2.Not be an object - in which case the `in` check will error.
        // If we hit either of these cases, we just ignore this mark and move on.
        const detailObj = JSON.parse(timingDetail);
        if (!('devtools' in detailObj)) {
            return null;
        }
        if (!Types.Extensions.isValidExtensionPayload(detailObj.devtools)) {
            return null;
        }
        return detailObj.devtools;
    }
    catch (e) {
        // No need to worry about this error, just discard this event and don't
        // treat it as having any useful information for the purposes of extensions
        return null;
    }
}
export function data() {
    return {
        entryToNode,
        extensionTrackData: [...extensionTrackData],
        extensionMarkers: [...extensionMarkers],
    };
}
export function deps() {
    return ['UserTimings'];
}
//# sourceMappingURL=ExtensionTraceDataHandler.js.map