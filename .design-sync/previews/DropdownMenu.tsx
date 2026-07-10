import * as React from 'react';
import {
  DropdownMenu,
  DropdownMenuTrigger,
  DropdownMenuContent,
  DropdownMenuLabel,
  DropdownMenuItem,
  DropdownMenuSeparator,
  DropdownMenuCheckboxItem,
  buttonVariants,
} from 'insight-ui';

// NOTE: DropdownMenuTrigger asChild + <Button> breaks Radix's anchor
// measurement (Button in button.tsx is not React.forwardRef, so Slot's ref
// attach silently fails and the content never gets positioned). Worked
// around by styling the trigger itself with buttonVariants() instead of
// nesting <Button> via asChild. See learnings — likely a source-level fix
// (wrap Button in forwardRef) needed project-wide.
export const TicketActionsMenu = () => (
  <DropdownMenu open>
    <DropdownMenuTrigger className={buttonVariants({ variant: 'outline' })}>
      상담 티켓 작업
    </DropdownMenuTrigger>
    <DropdownMenuContent
      onCloseAutoFocus={(e: any) => e.preventDefault()}
    >
      <DropdownMenuLabel>티켓 작업</DropdownMenuLabel>
      <DropdownMenuSeparator />
      <DropdownMenuItem>담당자 재배정</DropdownMenuItem>
      <DropdownMenuItem>상담 완료 처리</DropdownMenuItem>
      <DropdownMenuItem>예약으로 전환</DropdownMenuItem>
      <DropdownMenuSeparator />
      <DropdownMenuCheckboxItem checked>SLA 알림 받기</DropdownMenuCheckboxItem>
      <DropdownMenuSeparator />
      <DropdownMenuItem variant="destructive">티켓 삭제</DropdownMenuItem>
    </DropdownMenuContent>
  </DropdownMenu>
);
