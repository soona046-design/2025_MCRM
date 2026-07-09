import * as React from 'react';
import {
  Select,
  SelectTrigger,
  SelectValue,
  SelectContent,
  SelectGroup,
  SelectLabel,
  SelectItem,
  SelectSeparator,
} from 'insight-ui';

export const BranchSelect = () => (
  <Select open defaultValue="gangnam">
    <SelectTrigger style={{ width: 220 }}>
      <SelectValue placeholder="지점 선택" />
    </SelectTrigger>
    <SelectContent style={{ position: 'static', transform: 'none' }}>
      <SelectGroup>
        <SelectLabel>지점</SelectLabel>
        <SelectItem value="gangnam">강남점</SelectItem>
        <SelectItem value="bundang">분당점</SelectItem>
        <SelectItem value="ilsan">일산점</SelectItem>
      </SelectGroup>
      <SelectSeparator />
      <SelectGroup>
        <SelectLabel>진료 채널</SelectLabel>
        <SelectItem value="naver">네이버 검색광고</SelectItem>
        <SelectItem value="instagram">인스타그램</SelectItem>
      </SelectGroup>
    </SelectContent>
  </Select>
);
