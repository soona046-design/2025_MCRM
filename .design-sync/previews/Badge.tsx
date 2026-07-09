import * as React from 'react';
import { Badge } from 'insight-ui';

export const Variants = () => (
  <div style={{ display: 'flex', gap: 8, flexWrap: 'wrap', alignItems: 'center' }}>
    <Badge>신규</Badge>
    <Badge variant="secondary">상담중</Badge>
    <Badge variant="destructive">SLA 초과</Badge>
    <Badge variant="outline">계약완료</Badge>
  </div>
);

export const ChannelTags = () => (
  <div style={{ display: 'flex', gap: 8, flexWrap: 'wrap', alignItems: 'center' }}>
    <Badge variant="secondary">네이버 검색광고</Badge>
    <Badge variant="secondary">인스타그램</Badge>
    <Badge variant="outline">임플란트</Badge>
    <Badge variant="outline">교정</Badge>
  </div>
);

export const LeadRow = () => (
  <div style={{ display: 'flex', alignItems: 'center', gap: 10, fontSize: 13 }}>
    <span style={{ fontWeight: 500 }}>김민지</span>
    <Badge>신규 리드</Badge>
    <Badge variant="destructive">응답 지연</Badge>
  </div>
);
