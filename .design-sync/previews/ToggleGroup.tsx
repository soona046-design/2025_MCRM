import * as React from 'react';
import { ToggleGroup, ToggleGroupItem } from 'insight-ui';

export const SingleSelect = () => (
  <ToggleGroup type="single" defaultValue="week">
    <ToggleGroupItem value="day">일간</ToggleGroupItem>
    <ToggleGroupItem value="week">주간</ToggleGroupItem>
    <ToggleGroupItem value="month">월간</ToggleGroupItem>
  </ToggleGroup>
);

export const MultipleSelect = () => (
  <ToggleGroup type="multiple" defaultValue={['naver', 'instagram']}>
    <ToggleGroupItem value="naver">네이버</ToggleGroupItem>
    <ToggleGroupItem value="instagram">인스타그램</ToggleGroupItem>
    <ToggleGroupItem value="kakao">카카오</ToggleGroupItem>
  </ToggleGroup>
);

export const OutlineVariant = () => (
  <ToggleGroup type="single" variant="outline" defaultValue="implant">
    <ToggleGroupItem value="implant">임플란트</ToggleGroupItem>
    <ToggleGroupItem value="ortho">교정</ToggleGroupItem>
    <ToggleGroupItem value="conservative">보존치료</ToggleGroupItem>
  </ToggleGroup>
);
