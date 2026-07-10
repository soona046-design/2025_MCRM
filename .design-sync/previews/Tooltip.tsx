import * as React from 'react';
import { Tooltip, TooltipTrigger, TooltipContent, TooltipProvider, Badge, buttonVariants } from 'insight-ui';

// The DS Button/Badge are plain function components (no forwardRef), so
// `<TooltipTrigger asChild><Button/></TooltipTrigger>` can't attach the anchor
// ref and Radix positions the content off-screen. Wrapping the trigger child
// in a native <span> (which accepts a ref) lets Popper measure the anchor and
// place the tooltip correctly. Extra vertical padding keeps the bubble in-card.
export const SlaHintTooltip = () => (
  <div style={{ padding: '52px 40px' }}>
    <TooltipProvider>
      <Tooltip open>
        <TooltipTrigger asChild>
          <span style={{ display: 'inline-flex' }}>
            <Badge variant="destructive">SLA 초과</Badge>
          </span>
        </TooltipTrigger>
        <TooltipContent side="bottom">배정 후 30분 경과 · 즉시 응답 필요</TooltipContent>
      </Tooltip>
    </TooltipProvider>
  </div>
);

export const ButtonHelpTooltip = () => (
  <div style={{ padding: '52px 40px' }}>
    <TooltipProvider>
      <Tooltip open>
        <TooltipTrigger className={buttonVariants({ variant: 'outline', size: 'sm' })}>
          리포트 내보내기
        </TooltipTrigger>
        <TooltipContent side="bottom">최근 30일 채널 성과를 엑셀로 다운로드합니다</TooltipContent>
      </Tooltip>
    </TooltipProvider>
  </div>
);
