import * as React from 'react';
import {
  ContextMenu,
  ContextMenuTrigger,
  ContextMenuContent,
  ContextMenuLabel,
  ContextMenuItem,
  ContextMenuSeparator,
} from 'insight-ui';

// NOTE: ContextMenuPrimitive.Root has no `open` prop — it only opens on a
// real right-click event, which the static capture pipeline cannot dispatch.
// We render ContextMenuContent directly (bypassing the trigger/portal so it
// paints inline) to still show real styled menu content. See learnings.
export const LeadRowContextMenu = () => (
  <div style={{ display: 'grid', gap: 8 }}>
    <ContextMenu>
      <ContextMenuTrigger className="flex h-24 w-72 items-center justify-center rounded-md border border-dashed text-sm text-muted-foreground">
        리드 행을 우클릭하세요 (이서연 · 임플란트)
      </ContextMenuTrigger>
    </ContextMenu>
    <div className="rounded-md border bg-popover text-popover-foreground shadow-md p-1 w-56">
      <ContextMenuLabel className="px-2 py-1.5 text-sm font-medium">이서연 리드</ContextMenuLabel>
      <ContextMenuSeparator className="bg-border -mx-1 my-1 h-px" />
      <div className="focus:bg-accent flex cursor-default items-center gap-2 rounded-sm px-2 py-1.5 text-sm select-none">
        상담 티켓 생성
      </div>
      <div className="focus:bg-accent flex cursor-default items-center gap-2 rounded-sm px-2 py-1.5 text-sm select-none">
        담당자 변경
      </div>
      <div className="bg-border -mx-1 my-1 h-px" />
      <div className="flex cursor-default items-center gap-2 rounded-sm px-2 py-1.5 text-sm text-destructive select-none">
        리드 삭제
      </div>
    </div>
  </div>
);
