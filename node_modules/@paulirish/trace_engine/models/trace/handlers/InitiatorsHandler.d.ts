import * as Types from '../types/types.js';
export declare function reset(): void;
export declare function handleEvent(event: Types.Events.Event): void;
export declare function finalize(): Promise<void>;
export interface InitiatorsData {
    eventToInitiator: Map<Types.Events.Event, Types.Events.Event>;
    initiatorToEvents: Map<Types.Events.Event, Types.Events.Event[]>;
}
export declare function data(): InitiatorsData;
