import * as React from 'react';
import { Collapsible, CollapsibleTrigger, CollapsibleContent, Button } from 'insight-ui';

export const CommunicationLog = () => (
  <Collapsible defaultOpen style={{ width: 340 }}>
    <div style={{ display: 'flex', alignItems: 'center', justifyContent: 'space-between' }}>
      <span style={{ fontSize: 13, fontWeight: 600 }}>김민지 님 상담 이력 (3건)</span>
      <CollapsibleTrigger asChild>
        <Button variant="ghost" size="sm">펼치기/접기</Button>
      </CollapsibleTrigger>
    </div>
    <CollapsibleContent>
      <div style={{ display: 'flex', flexDirection: 'column', gap: 6, marginTop: 8 }}>
        {['07/08 14:20 전화 상담 · 임플란트 비용 문의', '07/06 10:05 카카오톡 · 예약 일정 조율', '07/03 16:40 문자 · 초진 안내 발송'].map((t) => (
          <div key={t} style={{ fontSize: 12.5, color: 'hsl(var(--muted-foreground))', border: '1px solid hsl(var(--border))', borderRadius: 6, padding: '6px 10px' }}>
            {t}
          </div>
        ))}
      </div>
    </CollapsibleContent>
  </Collapsible>
);

export const AdvancedFilter = () => (
  <Collapsible defaultOpen style={{ width: 300 }}>
    <CollapsibleTrigger asChild>
      <Button variant="outline" size="sm">고급 필터 옵션</Button>
    </CollapsibleTrigger>
    <CollapsibleContent>
      <div style={{ marginTop: 10, fontSize: 12.5, color: 'hsl(var(--muted-foreground))', display: 'flex', flexDirection: 'column', gap: 4 }}>
        <div>담당 매니저: 전체</div>
        <div>치료 유형: 교정</div>
        <div>유입 채널: 네이버, 인스타그램</div>
      </div>
    </CollapsibleContent>
  </Collapsible>
);
