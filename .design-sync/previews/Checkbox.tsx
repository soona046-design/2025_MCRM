import * as React from 'react';
import { Checkbox, Label } from 'insight-ui';

export const Basic = () => (
  <div style={{ display: 'flex', alignItems: 'center', gap: 8 }}>
    <Checkbox id="cb-1" />
    <Label htmlFor="cb-1">김민지 (임플란트 상담)</Label>
  </div>
);

export const Checked = () => (
  <div style={{ display: 'flex', alignItems: 'center', gap: 8 }}>
    <Checkbox id="cb-2" defaultChecked />
    <Label htmlFor="cb-2">이서준 (교정 상담)</Label>
  </div>
);

export const TicketList = () => (
  <div style={{ display: 'flex', flexDirection: 'column', gap: 10 }}>
    <div style={{ display: 'flex', alignItems: 'center', gap: 8 }}>
      <Checkbox id="cb-3" defaultChecked />
      <Label htmlFor="cb-3">박서연 – 보존치료 상담</Label>
    </div>
    <div style={{ display: 'flex', alignItems: 'center', gap: 8 }}>
      <Checkbox id="cb-4" />
      <Label htmlFor="cb-4">최유나 – 재상담 요청</Label>
    </div>
  </div>
);

export const Disabled = () => (
  <div style={{ display: 'flex', flexDirection: 'column', gap: 10 }}>
    <div style={{ display: 'flex', alignItems: 'center', gap: 8 }}>
      <Checkbox id="cb-5" disabled />
      <Label htmlFor="cb-5">배정 완료 (편집 불가)</Label>
    </div>
    <div style={{ display: 'flex', alignItems: 'center', gap: 8 }}>
      <Checkbox id="cb-6" disabled defaultChecked />
      <Label htmlFor="cb-6">상담 종료 (편집 불가)</Label>
    </div>
  </div>
);
