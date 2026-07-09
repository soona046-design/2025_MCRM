import * as React from 'react';
import { Input, Label } from 'insight-ui';

export const Basic = () => (
  <div style={{ display: 'flex', flexDirection: 'column', gap: 6, width: 260 }}>
    <Label htmlFor="lead-name">환자명</Label>
    <Input id="lead-name" placeholder="김민지" defaultValue="김민지" />
  </div>
);

export const WithPlaceholder = () => (
  <div style={{ display: 'flex', flexDirection: 'column', gap: 6, width: 260 }}>
    <Label htmlFor="lead-phone">연락처</Label>
    <Input id="lead-phone" placeholder="010-0000-0000" />
  </div>
);

export const Types = () => (
  <div style={{ display: 'flex', flexDirection: 'column', gap: 12, width: 260 }}>
    <div style={{ display: 'flex', flexDirection: 'column', gap: 6 }}>
      <Label htmlFor="lead-email">이메일</Label>
      <Input id="lead-email" type="email" placeholder="patient@example.com" />
    </div>
    <div style={{ display: 'flex', flexDirection: 'column', gap: 6 }}>
      <Label htmlFor="lead-budget">예상 견적(원)</Label>
      <Input id="lead-budget" type="number" placeholder="1500000" />
    </div>
  </div>
);

export const Disabled = () => (
  <div style={{ display: 'flex', flexDirection: 'column', gap: 6, width: 260 }}>
    <Label htmlFor="lead-code">환자 코드</Label>
    <Input id="lead-code" disabled value="PT-2026-0417" />
  </div>
);

export const Invalid = () => (
  <div style={{ display: 'flex', flexDirection: 'column', gap: 6, width: 260 }}>
    <Label htmlFor="lead-phone-err">연락처</Label>
    <Input id="lead-phone-err" aria-invalid defaultValue="010-12" />
  </div>
);
