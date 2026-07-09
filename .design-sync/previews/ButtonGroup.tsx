import * as React from 'react';
import { ButtonGroup, ButtonGroupSeparator, ButtonGroupText, Button } from 'insight-ui';

export const Horizontal = () => (
  <ButtonGroup>
    <Button variant="outline">일간</Button>
    <Button variant="outline">주간</Button>
    <Button variant="outline">월간</Button>
  </ButtonGroup>
);

export const WithSeparatorAndText = () => (
  <ButtonGroup>
    <ButtonGroupText>상태</ButtonGroupText>
    <ButtonGroupSeparator />
    <Button variant="outline">신규</Button>
    <Button variant="outline">진행중</Button>
    <Button variant="outline">완료</Button>
  </ButtonGroup>
);

export const Vertical = () => (
  <ButtonGroup orientation="vertical" style={{ width: 140 }}>
    <Button variant="outline">임플란트</Button>
    <Button variant="outline">교정</Button>
    <Button variant="outline">보존치료</Button>
  </ButtonGroup>
);
