import * as React from 'react';
import { Progress } from 'insight-ui';

export const Basic = () => (
  <div style={{ display: 'flex', flexDirection: 'column', gap: 6, width: 280 }}>
    <div style={{ display: 'flex', justifyContent: 'space-between', fontSize: 13 }}>
      <span>이번 달 목표 대비 신규 리드</span>
      <span style={{ color: 'var(--muted-foreground)' }}>68%</span>
    </div>
    <Progress value={68} />
  </div>
);

export const Values = () => (
  <div style={{ display: 'flex', flexDirection: 'column', gap: 14, width: 280 }}>
    <div style={{ display: 'flex', flexDirection: 'column', gap: 6 }}>
      <div style={{ fontSize: 12, color: 'var(--muted-foreground)' }}>임플란트 전환율</div>
      <Progress value={25} />
    </div>
    <div style={{ display: 'flex', flexDirection: 'column', gap: 6 }}>
      <div style={{ fontSize: 12, color: 'var(--muted-foreground)' }}>교정 전환율</div>
      <Progress value={55} />
    </div>
    <div style={{ display: 'flex', flexDirection: 'column', gap: 6 }}>
      <div style={{ fontSize: 12, color: 'var(--muted-foreground)' }}>보존치료 전환율</div>
      <Progress value={90} />
    </div>
  </div>
);

export const Complete = () => (
  <div style={{ display: 'flex', flexDirection: 'column', gap: 6, width: 280 }}>
    <div style={{ display: 'flex', justifyContent: 'space-between', fontSize: 13 }}>
      <span>월간 예산 소진율</span>
      <span style={{ color: 'var(--muted-foreground)' }}>100%</span>
    </div>
    <Progress value={100} />
  </div>
);
