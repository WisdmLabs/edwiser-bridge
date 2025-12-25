import * as Types from '../types/types.js';
import { type InsightModel, type InsightSetContext, type RequiredData } from './types.js';
export declare function deps(): ['SelectorStats'];
export type SlowCSSSelectorInsightModel = InsightModel<{
    totalElapsedMs: Types.Timing.MilliSeconds;
    totalMatchAttempts: number;
    totalMatchCount: number;
    topElapsedMs: Types.Events.SelectorTiming[];
    topMatchAttempts: Types.Events.SelectorTiming[];
}>;
export declare function generateInsight(parsedTrace: RequiredData<typeof deps>, context: InsightSetContext): SlowCSSSelectorInsightModel;
