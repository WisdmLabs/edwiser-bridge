import type { InsightModels, TraceInsightSets } from './types.js';
export declare function getInsight<InsightName extends keyof InsightModels>(insightName: InsightName, insights: TraceInsightSets | null, key: string | null): InsightModels[InsightName] | null;
