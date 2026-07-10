import * as React from 'react';
import { Button } from 'insight-ui';

export const Variants = () => (
  <div style={{ display: 'flex', gap: 12, flexWrap: 'wrap', alignItems: 'center' }}>
    <Button>리드 추가</Button>
    <Button variant="secondary">전체 캠페인</Button>
    <Button variant="outline">충전하기</Button>
    <Button variant="ghost">전체보기</Button>
    <Button variant="destructive">삭제</Button>
    <Button variant="link">자세히</Button>
  </div>
);

export const Sizes = () => (
  <div style={{ display: 'flex', gap: 12, alignItems: 'center' }}>
    <Button size="sm">작게</Button>
    <Button>기본</Button>
    <Button size="lg">크게</Button>
  </div>
);

export const States = () => (
  <div style={{ display: 'flex', gap: 12, alignItems: 'center' }}>
    <Button>활성</Button>
    <Button disabled>비활성</Button>
  </div>
);
