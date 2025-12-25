import * as Extras from '../extras/extras.js';
import type * as Types from '../types/types.js';
import { type InsightModel, type InsightSetContext, type RequiredData } from './types.js';
export declare function deps(): ['Meta', 'NetworkRequests', 'Renderer', 'ImagePainting'];
export type ThirdPartiesInsightModel = InsightModel<{
    entityByRequest: Map<Types.Events.SyntheticNetworkRequest, Extras.ThirdParties.Entity>;
    requestsByEntity: Map<Extras.ThirdParties.Entity, Types.Events.SyntheticNetworkRequest[]>;
    summaryByRequest: Map<Types.Events.SyntheticNetworkRequest, Extras.ThirdParties.Summary>;
    summaryByEntity: Map<Extras.ThirdParties.Entity, Extras.ThirdParties.Summary>;
    /** The entity for this navigation's URL. Any other entity is from a third party. */
    firstPartyEntity?: Extras.ThirdParties.Entity;
}>;
export declare function generateInsight(parsedTrace: RequiredData<typeof deps>, context: InsightSetContext): ThirdPartiesInsightModel;
