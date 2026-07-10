import * as React from 'react';
import {
  Item,
  ItemMedia,
  ItemContent,
  ItemTitle,
  ItemDescription,
  ItemActions,
  ItemGroup,
  ItemSeparator,
  Avatar,
  AvatarFallback,
  Button,
} from 'insight-ui';
import { Phone } from 'lucide-react';

export const LeadItem = () => (
  <Item variant="outline" style={{ maxWidth: 420 }}>
    <ItemMedia>
      <Avatar>
        <AvatarFallback>김민</AvatarFallback>
      </Avatar>
    </ItemMedia>
    <ItemContent>
      <ItemTitle>김민지</ItemTitle>
      <ItemDescription>네이버 검색광고 · 임플란트 문의</ItemDescription>
    </ItemContent>
    <ItemActions>
      <Button size="sm" variant="outline">배정</Button>
    </ItemActions>
  </Item>
);

export const IconMediaItem = () => (
  <Item variant="muted" style={{ maxWidth: 420 }}>
    <ItemMedia variant="icon">
      <Phone />
    </ItemMedia>
    <ItemContent>
      <ItemTitle>부재중 전화 2건</ItemTitle>
      <ItemDescription>이서준 상담매니저 · 10분 전</ItemDescription>
    </ItemContent>
  </Item>
);

export const GroupWithSeparator = () => (
  <ItemGroup style={{ maxWidth: 420, border: '1px solid var(--border)', borderRadius: 8 }}>
    <Item size="sm">
      <ItemMedia>
        <Avatar style={{ width: 28, height: 28 }}>
          <AvatarFallback style={{ fontSize: 11 }}>박</AvatarFallback>
        </Avatar>
      </ItemMedia>
      <ItemContent>
        <ItemTitle>박지훈 · 교정 상담 예약</ItemTitle>
        <ItemDescription>오늘 15:30</ItemDescription>
      </ItemContent>
    </Item>
    <ItemSeparator />
    <Item size="sm">
      <ItemMedia>
        <Avatar style={{ width: 28, height: 28 }}>
          <AvatarFallback style={{ fontSize: 11 }}>최</AvatarFallback>
        </Avatar>
      </ItemMedia>
      <ItemContent>
        <ItemTitle>최유나 · 정기검진</ItemTitle>
        <ItemDescription>오늘 16:00</ItemDescription>
      </ItemContent>
    </Item>
  </ItemGroup>
);
