// Copyright 2024 The Chromium Authors. All rights reserved.
// Use of this source code is governed by a BSD-style license that can be
// found in the LICENSE file.
// TODO(crbug.com/348449529): refactor to proper devtools module
import { FirstContentfulPaint } from './metrics/FirstContentfulPaint.js';
import { Interactive } from './metrics/Interactive.js';
import { LargestContentfulPaint } from './metrics/LargestContentfulPaint.js';
import { MaxPotentialFID } from './metrics/MaxPotentialFID.js';
import { Metric } from './metrics/Metric.js';
import { SpeedIndex } from './metrics/SpeedIndex.js';
import { TotalBlockingTime } from './metrics/TotalBlockingTime.js';
export { FirstContentfulPaint, Interactive, LargestContentfulPaint, MaxPotentialFID, Metric, SpeedIndex, TotalBlockingTime, };
export * as TBTUtils from './metrics/TBTUtils.js';
//# sourceMappingURL=MetricsModule.js.map