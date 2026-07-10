import * as React from 'react';
import { Slider, Label } from 'insight-ui';

export const Basic = () => (
  <div style={{ display: 'flex', flexDirection: 'column', gap: 8, width: 280 }}>
    <Label htmlFor="budget-slider">일일 광고 예산 (만원)</Label>
    <Slider id="budget-slider" defaultValue={[35]} max={100} step={5} />
  </div>
);

export const Range = () => (
  <div style={{ display: 'flex', flexDirection: 'column', gap: 8, width: 280 }}>
    <Label>예상 견적 범위 (만원)</Label>
    <Slider defaultValue={[150, 400]} max={600} step={10} />
  </div>
);

export const Disabled = () => (
  <div style={{ display: 'flex', flexDirection: 'column', gap: 8, width: 280 }}>
    <Label>리드 점수 임계값 (고정)</Label>
    <Slider defaultValue={[70]} max={100} disabled />
  </div>
);
