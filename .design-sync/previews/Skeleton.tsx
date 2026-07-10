import * as React from 'react';
import { Skeleton } from 'insight-ui';

export const TextLines = () => (
  <div style={{ display: 'flex', flexDirection: 'column', gap: 8, width: 260 }}>
    <Skeleton style={{ height: 14, width: '70%' }} />
    <Skeleton style={{ height: 14, width: '90%' }} />
    <Skeleton style={{ height: 14, width: '50%' }} />
  </div>
);

export const LeadCard = () => (
  <div style={{ display: 'flex', gap: 12, alignItems: 'center', width: 280 }}>
    <Skeleton style={{ height: 40, width: 40, borderRadius: '9999px' }} />
    <div style={{ display: 'flex', flexDirection: 'column', gap: 6, flex: 1 }}>
      <Skeleton style={{ height: 12, width: '60%' }} />
      <Skeleton style={{ height: 12, width: '40%' }} />
    </div>
  </div>
);

export const DashboardTile = () => (
  <div style={{ display: 'flex', flexDirection: 'column', gap: 8, width: 220 }}>
    <Skeleton style={{ height: 12, width: '45%' }} />
    <Skeleton style={{ height: 28, width: '65%' }} />
  </div>
);
