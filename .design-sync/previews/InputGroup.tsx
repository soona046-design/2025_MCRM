import * as React from 'react';
import {
  InputGroup,
  InputGroupInput,
  InputGroupAddon,
  InputGroupButton,
  InputGroupText,
  InputGroupTextarea,
} from 'insight-ui';
import { Search, X, Phone } from 'lucide-react';

export const SearchWithIcon = () => (
  <InputGroup style={{ maxWidth: 320 }}>
    <InputGroupAddon>
      <Search size={16} />
    </InputGroupAddon>
    <InputGroupInput placeholder="리드 이름, 연락처 검색" />
  </InputGroup>
);

export const WithClearButton = () => (
  <InputGroup style={{ maxWidth: 320 }}>
    <InputGroupAddon>
      <Phone size={16} />
    </InputGroupAddon>
    <InputGroupInput defaultValue="010-1234-5678" />
    <InputGroupAddon align="inline-end">
      <InputGroupButton aria-label="지우기">
        <X size={14} />
      </InputGroupButton>
    </InputGroupAddon>
  </InputGroup>
);

export const WithTextAddon = () => (
  <InputGroup style={{ maxWidth: 280 }}>
    <InputGroupAddon>
      <InputGroupText>₩</InputGroupText>
    </InputGroupAddon>
    <InputGroupInput placeholder="캠페인 예산" />
    <InputGroupAddon align="inline-end">
      <InputGroupText>원/일</InputGroupText>
    </InputGroupAddon>
  </InputGroup>
);

export const TextareaWithFooterAction = () => (
  <InputGroup style={{ maxWidth: 360 }}>
    <InputGroupTextarea placeholder="상담 메모를 입력하세요..." rows={3} />
    <InputGroupAddon align="block-end">
      <InputGroupButton size="sm">메모 저장</InputGroupButton>
    </InputGroupAddon>
  </InputGroup>
);
