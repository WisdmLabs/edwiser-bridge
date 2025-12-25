// Copyright 2024 The Chromium Authors. All rights reserved.
// Use of this source code is governed by a BSD-style license that can be
// found in the LICENSE file.
import * as Types from '../types/types.js';
/**
 * INSTEAD, you probably want `SourceMapsResolver.resolvedURLForEntry()`!
 * If an URL will be displayed in the UI, it's likely you should NOT use `getNonResolved`.
 *
 * Use `getNonResolved` method whenever resolving an URL's source mapping is not an
 * option. For example when processing non-ui data.
 *
 * TODO: migrate existing uses of this over to resolvedURLForEntry.
 */
export function getNonResolved(parsedTrace, entry) {
    if (Types.Events.isProfileCall(entry)) {
        return entry.callFrame.url;
    }
    if (entry.args?.data?.stackTrace && entry.args.data.stackTrace.length > 0) {
        return entry.args.data.stackTrace[0].url;
    }
    if (Types.Events.isSyntheticNetworkRequest(entry)) {
        return entry.args.data.url;
    }
    // DecodeImage events use the URL from the relevant PaintImage event.
    if (Types.Events.isDecodeImage(entry)) {
        const paintEvent = parsedTrace.ImagePainting.paintImageForEvent.get(entry);
        return paintEvent ? getNonResolved(parsedTrace, paintEvent) : null;
    }
    // DrawLazyPixelRef events use the URL from the relevant PaintImage event.
    if (Types.Events.isDrawLazyPixelRef(entry) && entry.args?.LazyPixelRef) {
        const paintEvent = parsedTrace.ImagePainting.paintImageByDrawLazyPixelRef.get(entry.args.LazyPixelRef);
        return paintEvent ? getNonResolved(parsedTrace, paintEvent) : null;
    }
    // ParseHTML events store the URL under beginData, not data.
    if (Types.Events.isParseHTML(entry)) {
        return entry.args.beginData.url;
    }
    // For all other events, try to see if the URL is provided, else return null.
    if (entry.args?.data?.url) {
        return entry.args.data.url;
    }
    return null;
}
//# sourceMappingURL=URLForEntry.js.map