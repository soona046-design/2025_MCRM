import * as React from 'react';
import {
  Empty,
  EmptyHeader,
  EmptyMedia,
  EmptyTitle,
  EmptyDescription,
  EmptyContent,
  Button,
} from 'insight-ui';
import { Inbox, Search } from 'lucide-react';

export const NoLeads = () => (
  <Empty style={{ border: '1px dashed var(--border)' }}>
    <EmptyHeader>
      <EmptyMedia variant="icon">
        <Inbox />
      </EmptyMedia>
      <EmptyTitle>신규 리드가 없습니다</EmptyTitle>
      <EmptyDescription>새 채널 캠페인이 시작되면 이곳에 리드가 표시됩니다.</EmptyDescription>
    </EmptyHeader>
    <EmptyContent>
      <Button size="sm">캠페인 만들기</Button>
    </EmptyContent>
  </Empty>
);

export const NoSearchResults = () => (
  <Empty style={{ border: '1px dashed var(--border)' }}>
    <EmptyHeader>
      <EmptyMedia variant="icon">
        <Search />
      </EmptyMedia>
      <EmptyTitle>검색 결과 없음</EmptyTitle>
      <EmptyDescription>"임플란트 상담" 검색어와 일치하는 티켓이 없습니다.</EmptyDescription>
    </EmptyHeader>
  </Empty>
);
