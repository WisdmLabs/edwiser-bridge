import type * as Platform from '../../../core/platform/platform.js';
import type * as Handlers from '../handlers/handlers.js';
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
export declare function getNonResolved(parsedTrace: Handlers.Types.ParsedTrace, entry: Types.Events.Event): Platform.DevToolsPath.UrlString | null;
