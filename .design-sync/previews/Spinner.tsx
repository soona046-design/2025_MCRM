import * as React from 'react';
import { Spinner } from 'insight-ui';

export const Basic = () => <Spinner />;

export const WithLabel = () => (
  <div style={{ display: 'flex', alignItems: 'center', gap: 8, fontSize: 13, color: 'var(--muted-foreground)' }}>
    <Spinner />
    <span>리드 목록 불러오는 중...</span>
  </div>
);

export const Sizes = () => (
  <div style={{ display: 'flex', alignItems: 'center', gap: 16 }}>
    <Spinner className="size-3" />
    <Spinner className="size-4" />
    <Spinner className="size-6" />
  </div>
);
