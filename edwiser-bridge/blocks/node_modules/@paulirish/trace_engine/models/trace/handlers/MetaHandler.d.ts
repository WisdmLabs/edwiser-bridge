import * as Types from '../types/types.js';
export declare function reset(): void;
export declare function handleEvent(event: Types.Events.Event): void;
export declare function finalize(): Promise<void>;
export type MetaHandlerData = {
    traceIsGeneric: boolean;
    traceBounds: Types.Timing.TraceWindowMicroSeconds;
    browserProcessId: Types.Events.ProcessID;
    processNames: Map<Types.Events.ProcessID, Types.Events.ProcessName>;
    browserThreadId: Types.Events.ThreadID;
    gpuProcessId: Types.Events.ProcessID;
    navigationsByFrameId: Map<string, Types.Events.NavigationStart[]>;
    navigationsByNavigationId: Map<string, Types.Events.NavigationStart>;
    threadsInProcess: Map<Types.Events.ProcessID, Map<Types.Events.ThreadID, Types.Events.ThreadName>>;
    mainFrameId: string;
    mainFrameURL: string;
    /**
     * A frame can have multiple renderer processes, at the same time,
     * a renderer process can have multiple URLs. This map tracks the
     * processes active on a given frame, with the time window in which
     * they were active. Because a renderer process might have multiple
     * URLs, each process in each frame has an array of windows, with an
     * entry for each URL it had.
     */
    rendererProcessesByFrame: FrameProcessData;
    topLevelRendererIds: Set<Types.Events.ProcessID>;
    frameByProcessId: Map<Types.Events.ProcessID, Map<string, Types.Events.TraceFrame>>;
    mainFrameNavigations: Types.Events.NavigationStart[];
    gpuThreadId?: Types.Events.ThreadID;
    viewportRect?: DOMRect;
    devicePixelRatio?: number;
};
export type FrameProcessData = Map<string, Map<Types.Events.ProcessID, {
    frame: Types.Events.TraceFrame;
    window: Types.Timing.TraceWindowMicroSeconds;
}[]>>;
export declare function data(): MetaHandlerData;
