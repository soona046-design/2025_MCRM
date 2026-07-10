import * as React from 'react';
import {
  Command,
  CommandInput,
  CommandList,
  CommandGroup,
  CommandItem,
  CommandSeparator,
  CommandShortcut,
  CommandEmpty,
} from 'insight-ui';

export const QuickActionPalette = () => (
  <Command style={{ width: 340, border: '1px solid hsl(var(--border))', borderRadius: 8 }}>
    <CommandInput placeholder="리드, 상담, 예약 검색..." />
    <CommandList>
      <CommandEmpty>검색 결과가 없습니다.</CommandEmpty>
      <CommandGroup heading="빠른 작업">
        <CommandItem>새 리드 등록<CommandShortcut>⌘N</CommandShortcut></CommandItem>
        <CommandItem>상담 티켓 배정<CommandShortcut>⌘T</CommandShortcut></CommandItem>
        <CommandItem>예약 잡기<CommandShortcut>⌘A</CommandShortcut></CommandItem>
      </CommandGroup>
      <CommandSeparator />
      <CommandGroup heading="최근 검색">
        <CommandItem>김민지 · 임플란트 상담</CommandItem>
        <CommandItem>네이버 채널 피벗 대시보드</CommandItem>
      </CommandGroup>
    </CommandList>
  </Command>
);

export const PatientSearch = () => (
  <Command style={{ width: 320, border: '1px solid hsl(var(--border))', borderRadius: 8 }}>
    <CommandInput placeholder="환자명 또는 전화번호" defaultValue="이서준" />
    <CommandList>
      <CommandGroup heading="검색 결과 (2)">
        <CommandItem>이서준 · 010-****-2231 · 교정</CommandItem>
        <CommandItem>이서연 · 010-****-8842 · 임플란트</CommandItem>
      </CommandGroup>
    </CommandList>
  </Command>
);
