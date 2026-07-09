import * as React from 'react';
import { ScrollArea, Badge } from 'insight-ui';

export const RecentLeadsList = () => (
  <ScrollArea style={{ height: 220, width: 300, border: '1px solid hsl(var(--border))', borderRadius: 8 }}>
    <div style={{ padding: 12, display: 'flex', flexDirection: 'column', gap: 10 }}>
      {[
        ['김민지', '임플란트', '네이버'],
        ['이서준', '교정', '인스타그램'],
        ['박하은', '보존치료', '네이버'],
        ['최지우', '임플란트', '소개'],
        ['정도윤', '교정', '인스타그램'],
        ['한소율', '임플란트', '네이버'],
        ['오유진', '보존치료', '홈페이지'],
        ['강태현', '교정', '네이버'],
      ].map(([name, tx, ch]) => (
        <div key={name} style={{ display: 'flex', alignItems: 'center', justifyContent: 'space-between', fontSize: 12.5 }}>
          <span style={{ fontWeight: 500 }}>{name}</span>
          <Badge variant="secondary">{tx}</Badge>
          <span style={{ color: 'hsl(var(--muted-foreground))' }}>{ch}</span>
        </div>
      ))}
    </div>
  </ScrollArea>
);

export const NotificationFeed = () => (
  <ScrollArea style={{ height: 180, width: 320, border: '1px solid hsl(var(--border))', borderRadius: 8 }}>
    <div style={{ padding: '10px 14px', display: 'flex', flexDirection: 'column', gap: 8 }}>
      {[
        'SLA 위반 임박: 이서준 리드 (5분 남음)',
        '신규 리드 배정: 박하은 → 김상담',
        '예약 확정: 최지우 · 07/10 14:00',
        '결제 완료: 정도윤 · ₩3,200,000',
        '리마인더 발송: 한소율 · 07/09 09:00',
      ].map((t) => (
        <div key={t} style={{ fontSize: 12.5, color: 'hsl(var(--foreground))', paddingBottom: 8, borderBottom: '1px solid hsl(var(--border))' }}>
          {t}
        </div>
      ))}
    </div>
  </ScrollArea>
);
