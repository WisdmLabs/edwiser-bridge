import type * as Types from '../types/types.js';
import { type InsightModel, type InsightSetContext, type RequiredData } from './types.js';
export declare function deps(): ['Meta', 'UserInteractions'];
export type ViewportInsightModel = InsightModel<{
    mobileOptimized: boolean | null;
    viewportEvent?: Types.Events.ParseMetaViewport;
}>;
export declare function generateInsight(parsedTrace: RequiredData<typeof deps>, context: InsightSetContext): ViewportInsightModel;
