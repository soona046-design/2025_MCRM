import * as React from 'react';
import { AspectRatio } from 'insight-ui';

export const Widescreen = () => (
  <div style={{ width: 320 }}>
    <AspectRatio ratio={16 / 9} style={{ borderRadius: 8, overflow: 'hidden', background: 'var(--muted)' }}>
      <img
        src="https://images.unsplash.com/photo-1629909613654-28e377c37b09?w=640&h=360&fit=crop"
        alt="임플란트 상담실"
        style={{ width: '100%', height: '100%', objectFit: 'cover' }}
      />
    </AspectRatio>
  </div>
);

export const Square = () => (
  <div style={{ width: 200 }}>
    <AspectRatio ratio={1} style={{ borderRadius: 8, overflow: 'hidden', background: 'var(--muted)' }}>
      <img
        src="https://images.unsplash.com/photo-1606811841689-23dfddce3e95?w=400&h=400&fit=crop"
        alt="교정 전후 사진"
        style={{ width: '100%', height: '100%', objectFit: 'cover' }}
      />
    </AspectRatio>
  </div>
);

export const Portrait = () => (
  <div style={{ width: 180 }}>
    <AspectRatio ratio={3 / 4} style={{ borderRadius: 8, overflow: 'hidden', background: 'var(--muted)' }}>
      <img
        src="https://images.unsplash.com/photo-1588776814546-1ffcf47267a5?w=400&h=533&fit=crop"
        alt="네이버 광고 소재"
        style={{ width: '100%', height: '100%', objectFit: 'cover' }}
      />
    </AspectRatio>
  </div>
);
