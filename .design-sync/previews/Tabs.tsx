import * as React from 'react';
import { Tabs, TabsList, TabsTrigger, TabsContent } from 'insight-ui';

export const LeadFunnelTabs = () => (
  <Tabs defaultValue="consult" style={{ width: 360 }}>
    <TabsList>
      <TabsTrigger value="visit">방문</TabsTrigger>
      <TabsTrigger value="consult">상담</TabsTrigger>
      <TabsTrigger value="appt">예약</TabsTrigger>
      <TabsTrigger value="pay">결제</TabsTrigger>
    </TabsList>
    <TabsContent value="consult">
      <div style={{ fontSize: 13, padding: '12px 2px', color: 'hsl(var(--foreground))' }}>
        <div style={{ fontWeight: 600, marginBottom: 4 }}>상담 진행중 · 42건</div>
        <div style={{ color: 'hsl(var(--muted-foreground))' }}>임플란트 상담 18건, 교정 상담 24건</div>
      </div>
    </TabsContent>
  </Tabs>
);

export const DashboardPeriodTabs = () => (
  <Tabs defaultValue="week" style={{ width: 320 }}>
    <TabsList>
      <TabsTrigger value="today">오늘</TabsTrigger>
      <TabsTrigger value="week">이번 주</TabsTrigger>
      <TabsTrigger value="month">이번 달</TabsTrigger>
    </TabsList>
    <TabsContent value="week">
      <div style={{ fontSize: 24, fontWeight: 600, padding: '12px 2px' }}>₩12.4M</div>
      <div style={{ fontSize: 12, color: 'hsl(var(--muted-foreground))', padding: '0 2px' }}>
        네이버 · 인스타그램 광고비 합계
      </div>
    </TabsContent>
  </Tabs>
);
