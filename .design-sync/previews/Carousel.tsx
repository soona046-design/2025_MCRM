import * as React from 'react';
import { Carousel, CarouselContent, CarouselItem, CarouselPrevious, CarouselNext } from 'insight-ui';

export const ClinicGalleryCarousel = () => (
  <Carousel style={{ width: 340 }}>
    <CarouselContent>
      {['강남점 상담실', '역삼점 진료실', '분당점 대기실'].map((t) => (
        <CarouselItem key={t} style={{ maxWidth: 280 }}>
          <div
            style={{
              height: 140,
              borderRadius: 8,
              background: 'hsl(var(--muted))',
              display: 'flex',
              alignItems: 'center',
              justifyContent: 'center',
              fontSize: 13,
              color: 'hsl(var(--muted-foreground))',
              fontWeight: 500,
            }}
          >
            {t}
          </div>
        </CarouselItem>
      ))}
    </CarouselContent>
    <CarouselPrevious />
    <CarouselNext />
  </Carousel>
);

export const KpiSummaryCarousel = () => (
  <Carousel style={{ width: 320 }}>
    <CarouselContent>
      {[
        ['신규 리드', '128건'],
        ['상담 전환율', '58%'],
        ['이번 달 매출', '₩38.2M'],
      ].map(([label, value]) => (
        <CarouselItem key={label} style={{ maxWidth: 260 }}>
          <div style={{ border: '1px solid hsl(var(--border))', borderRadius: 8, padding: 16 }}>
            <div style={{ fontSize: 12, color: 'hsl(var(--muted-foreground))' }}>{label}</div>
            <div style={{ fontSize: 22, fontWeight: 600, marginTop: 4 }}>{value}</div>
          </div>
        </CarouselItem>
      ))}
    </CarouselContent>
  </Carousel>
);
