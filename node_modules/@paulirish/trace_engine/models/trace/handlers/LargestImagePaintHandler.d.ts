import type * as Protocol from '../../../generated/protocol.js';
import * as Types from '../types/types.js';
import type { HandlerName } from './types.js';
export declare function reset(): void;
export declare function handleEvent(event: Types.Events.Event): void;
export declare function finalize(): Promise<void>;
export interface LargestImagePaintData {
    imageByDOMNodeId: Map<Protocol.DOM.BackendNodeId, Types.Events.LargestImagePaintCandidate>;
    lcpRequestByNavigation: Map<Types.Events.NavigationStart | null, Types.Events.SyntheticNetworkRequest>;
}
export declare function data(): LargestImagePaintData;
export declare function deps(): HandlerName[];
