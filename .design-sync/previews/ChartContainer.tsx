import * as React from 'react';
import { ChartContainer, ChartTooltip, ChartTooltipContent, type ChartConfig } from 'insight-ui';
import { Bar, BarChart, CartesianGrid, XAxis, Line, LineChart } from 'recharts';

const weeklyLeadsData = [
  { week: '1주차', naver: 18, instagram: 9 },
  { week: '2주차', naver: 22, instagram: 12 },
  { week: '3주차', naver: 19, instagram: 15 },
  { week: '4주차', naver: 27, instagram: 11 },
];

const weeklyLeadsConfig = {
  naver: { label: '네이버', color: '#FF5B2C' },
  instagram: { label: '인스타그램', color: '#2C7CFF' },
} satisfies ChartConfig;

export const WeeklyLeadsByChannel = () => (
  <ChartContainer config={weeklyLeadsConfig} style={{ width: 380, height: 220 }}>
    <BarChart data={weeklyLeadsData}>
      <CartesianGrid vertical={false} />
      <XAxis dataKey="week" tickLine={false} axisLine={false} tickMargin={8} />
      <ChartTooltip content={<ChartTooltipContent />} />
      <Bar dataKey="naver" fill="var(--color-naver)" radius={4} />
      <Bar dataKey="instagram" fill="var(--color-instagram)" radius={4} />
    </BarChart>
  </ChartContainer>
);

const revenueTrendData = [
  { month: '3월', revenue: 28.4 },
  { month: '4월', revenue: 31.2 },
  { month: '5월', revenue: 29.8 },
  { month: '6월', revenue: 35.1 },
  { month: '7월', revenue: 38.2 },
];

const revenueTrendConfig = {
  revenue: { label: '매출 (백만원)', color: '#FF5B2C' },
} satisfies ChartConfig;

export const MonthlyRevenueTrend = () => (
  <ChartContainer config={revenueTrendConfig} style={{ width: 380, height: 220 }}>
    <LineChart data={revenueTrendData}>
      <CartesianGrid vertical={false} />
      <XAxis dataKey="month" tickLine={false} axisLine={false} tickMargin={8} />
      <ChartTooltip content={<ChartTooltipContent />} />
      <Line type="monotone" dataKey="revenue" stroke="var(--color-revenue)" strokeWidth={2} dot={{ r: 3 }} />
    </LineChart>
  </ChartContainer>
);
