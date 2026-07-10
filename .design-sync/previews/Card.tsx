import * as React from 'react';
import {
  Card,
  CardHeader,
  CardTitle,
  CardDescription,
  CardContent,
  CardFooter,
  CardAction,
  Button,
} from 'insight-ui';

export const StatCard = () => (
  <Card style={{ maxWidth: 340 }}>
    <CardHeader>
      <CardTitle>이번 달 예상 매출</CardTitle>
      <CardDescription>2026년 7월 · 강남점</CardDescription>
    </CardHeader>
    <CardContent>
      <div style={{ fontSize: 28, fontWeight: 600, letterSpacing: '-0.5px' }}>₩38.2M</div>
      <p style={{ color: 'hsl(var(--muted-foreground))', marginTop: 4, fontSize: 13 }}>전월 대비 +9%</p>
    </CardContent>
    <CardFooter>
      <Button size="sm">리포트 보기</Button>
    </CardFooter>
  </Card>
);

export const WithAction = () => (
  <Card style={{ maxWidth: 340 }}>
    <CardHeader>
      <CardTitle>신규 리드</CardTitle>
      <CardDescription>최근 7일 유입</CardDescription>
      <CardAction>
        <Button variant="ghost" size="sm">전체보기</Button>
      </CardAction>
    </CardHeader>
    <CardContent>
      <div style={{ fontSize: 28, fontWeight: 600 }}>128</div>
    </CardContent>
  </Card>
);
