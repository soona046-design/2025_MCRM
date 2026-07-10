import * as React from 'react';
import { Switch, Label } from 'insight-ui';

export const Basic = () => (
  <div style={{ display: 'flex', alignItems: 'center', gap: 8 }}>
    <Switch id="sla-alert" defaultChecked />
    <Label htmlFor="sla-alert">SLA 위반 알림</Label>
  </div>
);

export const States = () => (
  <div style={{ display: 'flex', flexDirection: 'column', gap: 12 }}>
    <div style={{ display: 'flex', alignItems: 'center', gap: 8 }}>
      <Switch id="sw-on" defaultChecked />
      <Label htmlFor="sw-on">문자 자동 발송 켜짐</Label>
    </div>
    <div style={{ display: 'flex', alignItems: 'center', gap: 8 }}>
      <Switch id="sw-off" />
      <Label htmlFor="sw-off">카카오 알림톡 꺼짐</Label>
    </div>
  </div>
);

export const Disabled = () => (
  <div style={{ display: 'flex', flexDirection: 'column', gap: 12 }}>
    <div style={{ display: 'flex', alignItems: 'center', gap: 8 }}>
      <Switch id="sw-dis-on" defaultChecked disabled />
      <Label htmlFor="sw-dis-on">자동 배정 (권한 없음)</Label>
    </div>
    <div style={{ display: 'flex', alignItems: 'center', gap: 8 }}>
      <Switch id="sw-dis-off" disabled />
      <Label htmlFor="sw-dis-off">야간 상담 예약 (비활성)</Label>
    </div>
  </div>
);
