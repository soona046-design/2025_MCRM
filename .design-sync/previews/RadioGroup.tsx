import * as React from 'react';
import { RadioGroup, RadioGroupItem } from 'insight-ui';

export const Default = () => (
  <RadioGroup defaultValue="phone" style={{ gap: 12 }}>
    <div style={{ display: 'flex', alignItems: 'center', gap: 8 }}>
      <RadioGroupItem value="phone" id="contact-phone" />
      <label htmlFor="contact-phone" style={{ fontSize: 14 }}>전화 상담</label>
    </div>
    <div style={{ display: 'flex', alignItems: 'center', gap: 8 }}>
      <RadioGroupItem value="visit" id="contact-visit" />
      <label htmlFor="contact-visit" style={{ fontSize: 14 }}>방문 상담</label>
    </div>
    <div style={{ display: 'flex', alignItems: 'center', gap: 8 }}>
      <RadioGroupItem value="online" id="contact-online" />
      <label htmlFor="contact-online" style={{ fontSize: 14 }}>온라인 상담</label>
    </div>
  </RadioGroup>
);

export const LeadPriority = () => (
  <RadioGroup defaultValue="high" style={{ gap: 12 }}>
    <div style={{ display: 'flex', alignItems: 'center', gap: 8 }}>
      <RadioGroupItem value="high" id="priority-high" />
      <label htmlFor="priority-high" style={{ fontSize: 14 }}>긴급 (당일 응대)</label>
    </div>
    <div style={{ display: 'flex', alignItems: 'center', gap: 8 }}>
      <RadioGroupItem value="normal" id="priority-normal" />
      <label htmlFor="priority-normal" style={{ fontSize: 14 }}>일반</label>
    </div>
    <div style={{ display: 'flex', alignItems: 'center', gap: 8 }}>
      <RadioGroupItem value="low" id="priority-low" disabled />
      <label htmlFor="priority-low" style={{ fontSize: 14, opacity: 0.5 }}>낮음 (비활성)</label>
    </div>
  </RadioGroup>
);

export const Horizontal = () => (
  <RadioGroup defaultValue="gangnam" style={{ display: 'flex', flexDirection: 'row', gap: 20 }}>
    <div style={{ display: 'flex', alignItems: 'center', gap: 8 }}>
      <RadioGroupItem value="gangnam" id="branch-gangnam" />
      <label htmlFor="branch-gangnam" style={{ fontSize: 14 }}>강남점</label>
    </div>
    <div style={{ display: 'flex', alignItems: 'center', gap: 8 }}>
      <RadioGroupItem value="bundang" id="branch-bundang" />
      <label htmlFor="branch-bundang" style={{ fontSize: 14 }}>분당점</label>
    </div>
  </RadioGroup>
);
