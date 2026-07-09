import * as React from 'react';
import { Popover, PopoverTrigger, PopoverContent, buttonVariants, Button, Label, Input } from 'insight-ui';

// NOTE: PopoverTrigger asChild + <Button> breaks Radix's Popper anchor
// measurement because Button (button.tsx) is a plain function component,
// not React.forwardRef — Slot's ref attach silently fails, so the content
// never gets positioned (stuck at position:static, off-screen). Worked
// around here by rendering PopoverTrigger itself styled with
// buttonVariants() instead of nesting a <Button> via asChild. See
// learnings — this is a source-level fix candidate (wrap Button in
// forwardRef) affecting every asChild+Button composition project-wide.
export const ChannelFilterPopover = () => (
  <Popover open>
    <PopoverTrigger className={buttonVariants({ variant: 'outline' })}>
      채널 필터
    </PopoverTrigger>
    <PopoverContent
      onOpenAutoFocus={(e: any) => e.preventDefault()}
    >
      <div style={{ display: 'grid', gap: 10 }}>
        <div>
          <h4 style={{ margin: 0, fontSize: 14, fontWeight: 600 }}>채널 필터</h4>
          <p style={{ margin: '4px 0 0', fontSize: 12.5, color: 'var(--muted-foreground)' }}>
            분석에 포함할 유입 채널을 선택하세요.
          </p>
        </div>
        <div style={{ display: 'grid', gap: 6 }}>
          <Label htmlFor="pop-channel">채널명</Label>
          <Input id="pop-channel" defaultValue="네이버, 인스타그램" />
        </div>
        <div style={{ display: 'flex', justifyContent: 'flex-end', gap: 8 }}>
          <Button size="sm" variant="outline">
            초기화
          </Button>
          <Button size="sm">적용</Button>
        </div>
      </div>
    </PopoverContent>
  </Popover>
);
