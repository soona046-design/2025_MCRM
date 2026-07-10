import * as React from 'react';
import { HoverCard, HoverCardTrigger, HoverCardContent, Badge } from 'insight-ui';

export const AgentPreviewCard = () => (
  <HoverCard open>
    <HoverCardTrigger asChild>
      <span style={{ fontSize: 13, fontWeight: 500, textDecoration: 'underline', cursor: 'pointer' }}>
        김상담 매니저
      </span>
    </HoverCardTrigger>
    <HoverCardContent>
      <div style={{ display: 'flex', flexDirection: 'column', gap: 6 }}>
        <div style={{ fontWeight: 600, fontSize: 14 }}>김상담</div>
        <div style={{ fontSize: 12.5, color: 'hsl(var(--muted-foreground))' }}>강남점 · 상담매니저</div>
        <div style={{ fontSize: 12.5 }}>담당 리드 32건 · 평균 응답 8분</div>
        <Badge variant="secondary" style={{ width: 'fit-content' }}>이번 달 전환율 61%</Badge>
      </div>
    </HoverCardContent>
  </HoverCard>
);

export const LeadPreviewCard = () => (
  <HoverCard open>
    <HoverCardTrigger asChild>
      <span style={{ fontSize: 13, fontWeight: 500, textDecoration: 'underline', cursor: 'pointer' }}>
        박하은 리드
      </span>
    </HoverCardTrigger>
    <HoverCardContent>
      <div style={{ display: 'flex', flexDirection: 'column', gap: 6 }}>
        <div style={{ fontWeight: 600, fontSize: 14 }}>박하은</div>
        <div style={{ fontSize: 12.5, color: 'hsl(var(--muted-foreground))' }}>보존치료 문의 · 네이버 유입</div>
        <div style={{ fontSize: 12.5 }}>최초 방문: 2026-07-05</div>
      </div>
    </HoverCardContent>
  </HoverCard>
);
