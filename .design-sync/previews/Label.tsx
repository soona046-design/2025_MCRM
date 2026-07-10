import * as React from 'react';
import { Label, Input, Checkbox, Switch } from 'insight-ui';

export const Basic = () => (
  <Label htmlFor="clinic-name">지점명</Label>
);

export const WithInput = () => (
  <div style={{ display: 'flex', flexDirection: 'column', gap: 6, width: 240 }}>
    <Label htmlFor="clinic-name-2">지점명</Label>
    <Input id="clinic-name-2" defaultValue="강남점" />
  </div>
);

export const WithCheckbox = () => (
  <div style={{ display: 'flex', alignItems: 'center', gap: 8 }}>
    <Checkbox id="agree-marketing" defaultChecked />
    <Label htmlFor="agree-marketing">마케팅 정보 수신 동의</Label>
  </div>
);

export const Disabled = () => (
  <div style={{ display: 'flex', alignItems: 'center', gap: 8 }} className="group" data-disabled="true">
    <Switch id="sla-toggle" disabled />
    <Label htmlFor="sla-toggle" data-disabled="true">SLA 자동 알림 (비활성)</Label>
  </div>
);
