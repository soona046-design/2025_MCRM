import * as React from 'react';
import { Toggle } from 'insight-ui';
import { Star, Bold, Bell } from 'lucide-react';

export const Variants = () => (
  <div style={{ display: 'flex', gap: 12, alignItems: 'center' }}>
    <Toggle aria-label="즐겨찾기">
      <Star />
    </Toggle>
    <Toggle variant="outline" aria-label="강조">
      <Bold />
    </Toggle>
  </div>
);

export const Sizes = () => (
  <div style={{ display: 'flex', gap: 12, alignItems: 'center' }}>
    <Toggle size="sm" aria-label="작게">
      <Bell />
    </Toggle>
    <Toggle size="default" aria-label="기본">
      <Bell />
    </Toggle>
    <Toggle size="lg" aria-label="크게">
      <Bell />
    </Toggle>
  </div>
);

export const PressedAndDisabled = () => (
  <div style={{ display: 'flex', gap: 12, alignItems: 'center' }}>
    <Toggle variant="outline" defaultPressed aria-label="알림 켜짐">
      <Bell /> 알림 켜짐
    </Toggle>
    <Toggle variant="outline" disabled aria-label="비활성">
      <Star /> 비활성
    </Toggle>
  </div>
);
