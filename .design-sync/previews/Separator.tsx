import * as React from 'react';
import { Separator } from 'insight-ui';

export const Horizontal = () => (
  <div style={{ width: 280 }}>
    <div style={{ fontSize: 14, fontWeight: 600 }}>강남점</div>
    <div style={{ fontSize: 12, color: 'var(--muted-foreground)', marginBottom: 8 }}>
      상담 매니저: 박지현
    </div>
    <Separator />
    <div style={{ fontSize: 13, marginTop: 8 }}>이번 달 리드 128건 · 전환율 24%</div>
  </div>
);

export const Vertical = () => (
  <div style={{ display: 'flex', alignItems: 'center', height: 40, gap: 12 }}>
    <span style={{ fontSize: 13 }}>임플란트</span>
    <Separator orientation="vertical" />
    <span style={{ fontSize: 13 }}>교정</span>
    <Separator orientation="vertical" />
    <span style={{ fontSize: 13 }}>보존치료</span>
  </div>
);
