import * as ThirdPartyWeb from '../../../third_party/third-party-web/third-party-web.js';
import type * as Handlers from '../handlers/handlers.js';
import * as Types from '../types/types.js';
export type Entity = typeof ThirdPartyWeb.ThirdPartyWeb.entities[number];
export interface Summary {
    transferSize: number;
    mainThreadTime: Types.Timing.MicroSeconds;
}
export interface SummaryMaps {
    byEntity: Map<Entity, Summary>;
    byRequest: Map<Types.Events.SyntheticNetworkRequest, Summary>;
    requestsByEntity: Map<Entity, Types.Events.SyntheticNetworkRequest[]>;
}
export declare function makeUpEntity(entityCache: Map<string, Entity>, url: string): Entity | undefined;
export declare function getEntitiesByRequest(requests: Types.Events.SyntheticNetworkRequest[]): {
    entityByRequest: Map<Types.Events.SyntheticNetworkRequest, Entity>;
    madeUpEntityCache: Map<string, Entity>;
};
export declare function getSummariesAndEntitiesForTraceBounds(parsedTrace: Handlers.Types.ParsedTrace, traceBounds: Types.Timing.TraceWindowMicroSeconds, networkRequests: Types.Events.SyntheticNetworkRequest[]): {
    summaries: SummaryMaps;
    entityByRequest: Map<Types.Events.SyntheticNetworkRequest, Entity>;
    madeUpEntityCache: Map<string, Entity>;
};
