import type * as Protocol from '../../../generated/protocol.js';
import * as Types from '../types/types.js';
import { type InsightModel, type InsightSetContext, type RequiredData } from './types.js';
export type CLSCulpritsInsightModel = InsightModel<{
    animationFailures: readonly NoncompositedAnimationFailure[];
    shifts: Map<Types.Events.SyntheticLayoutShift, LayoutShiftRootCausesData>;
    clusters: Types.Events.SyntheticLayoutShiftCluster[];
    worstCluster: Types.Events.SyntheticLayoutShiftCluster | undefined;
}>;
export declare function deps(): ['Meta', 'Animations', 'LayoutShifts', 'NetworkRequests'];
export declare const enum AnimationFailureReasons {
    ACCELERATED_ANIMATIONS_DISABLED = "ACCELERATED_ANIMATIONS_DISABLED",
    EFFECT_SUPPRESSED_BY_DEVTOOLS = "EFFECT_SUPPRESSED_BY_DEVTOOLS",
    INVALID_ANIMATION_OR_EFFECT = "INVALID_ANIMATION_OR_EFFECT",
    EFFECT_HAS_UNSUPPORTED_TIMING_PARAMS = "EFFECT_HAS_UNSUPPORTED_TIMING_PARAMS",
    EFFECT_HAS_NON_REPLACE_COMPOSITE_MODE = "EFFECT_HAS_NON_REPLACE_COMPOSITE_MODE",
    TARGET_HAS_INVALID_COMPOSITING_STATE = "TARGET_HAS_INVALID_COMPOSITING_STATE",
    TARGET_HAS_INCOMPATIBLE_ANIMATIONS = "TARGET_HAS_INCOMPATIBLE_ANIMATIONS",
    TARGET_HAS_CSS_OFFSET = "TARGET_HAS_CSS_OFFSET",
    ANIMATION_AFFECTS_NON_CSS_PROPERTIES = "ANIMATION_AFFECTS_NON_CSS_PROPERTIES",
    TRANSFORM_RELATED_PROPERTY_CANNOT_BE_ACCELERATED_ON_TARGET = "TRANSFORM_RELATED_PROPERTY_CANNOT_BE_ACCELERATED_ON_TARGET",
    TRANSFROM_BOX_SIZE_DEPENDENT = "TRANSFROM_BOX_SIZE_DEPENDENT",
    FILTER_RELATED_PROPERTY_MAY_MOVE_PIXELS = "FILTER_RELATED_PROPERTY_MAY_MOVE_PIXELS",
    UNSUPPORTED_CSS_PROPERTY = "UNSUPPORTED_CSS_PROPERTY",
    MIXED_KEYFRAME_VALUE_TYPES = "MIXED_KEYFRAME_VALUE_TYPES",
    TIMELINE_SOURCE_HAS_INVALID_COMPOSITING_STATE = "TIMELINE_SOURCE_HAS_INVALID_COMPOSITING_STATE",
    ANIMATION_HAS_NO_VISIBLE_CHANGE = "ANIMATION_HAS_NO_VISIBLE_CHANGE",
    AFFECTS_IMPORTANT_PROPERTY = "AFFECTS_IMPORTANT_PROPERTY",
    SVG_TARGET_HAS_INDEPENDENT_TRANSFORM_PROPERTY = "SVG_TARGET_HAS_INDEPENDENT_TRANSFORM_PROPERTY"
}
export interface NoncompositedAnimationFailure {
    /**
     * Animation name.
     */
    name?: string;
    /**
     * Failure reason based on mask number defined in
     * https://source.chromium.org/search?q=f:compositor_animations.h%20%22enum%20FailureReason%22.
     */
    failureReasons: AnimationFailureReasons[];
    /**
     * Unsupported properties.
     */
    unsupportedProperties?: Types.Events.Animation['args']['data']['unsupportedProperties'];
    /**
     * Animation event.
     */
    animation?: Types.Events.SyntheticAnimationPair;
}
export interface LayoutShiftRootCausesData {
    iframeIds: string[];
    fontRequests: Types.Events.SyntheticNetworkRequest[];
    nonCompositedAnimations: NoncompositedAnimationFailure[];
    unsizedImages: Protocol.DOM.BackendNodeId[];
}
export declare function getNonCompositedFailure(animationEvent: Types.Events.SyntheticAnimationPair): NoncompositedAnimationFailure[];
export declare function generateInsight(parsedTrace: RequiredData<typeof deps>, context: InsightSetContext): CLSCulpritsInsightModel;
