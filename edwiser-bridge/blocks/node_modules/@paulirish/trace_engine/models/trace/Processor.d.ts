import * as Handlers from './handlers/handlers.js';
import * as Insights from './insights/insights.js';
import type * as Model from './ModelImpl.js';
import * as Types from './types/types.js';
export declare class TraceParseProgressEvent extends Event {
    data: Model.TraceParseEventProgressData;
    static readonly eventName = "traceparseprogress";
    constructor(data: Model.TraceParseEventProgressData, init?: EventInit);
}
declare global {
    interface HTMLElementEventMap {
        [TraceParseProgressEvent.eventName]: TraceParseProgressEvent;
    }
}
export interface ParseOptions {
    /**
     * If the trace was just recorded on the current page, rather than an imported file.
     * TODO(paulirish): Maybe remove. This is currently unused by the Processor and Handlers
     * @default false
     */
    isFreshRecording?: boolean;
    /**
     * If the trace is a CPU Profile rather than a Chrome tracing trace.
     * @default false
     */
    isCPUProfile?: boolean;
}
export declare class TraceProcessor extends EventTarget {
    #private;
    static createWithAllHandlers(): TraceProcessor;
    static getEnabledInsightRunners(parsedTrace: Handlers.Types.ParsedTrace): Partial<Insights.Types.InsightModelsType>;
    constructor(traceHandlers: Partial<Handlers.Types.Handlers>, modelConfiguration?: Types.Configuration.Configuration);
    reset(): void;
    parse(traceEvents: readonly Types.Events.Event[], options: ParseOptions): Promise<void>;
    get parsedTrace(): Handlers.Types.ParsedTrace | null;
    get insights(): Insights.Types.TraceInsightSets | null;
}
/**
 * Some Handlers need data provided by others. Dependencies of a handler handler are
 * declared in the `deps` field.
 * @returns A map from trace event handler name to trace event hander whose entries
 * iterate in such a way that each handler is visited after its dependencies.
 */
export declare function sortHandlers(traceHandlers: Partial<{
    [key in Handlers.Types.HandlerName]: Handlers.Types.Handler;
}>): Map<Handlers.Types.HandlerName, Handlers.Types.Handler>;
